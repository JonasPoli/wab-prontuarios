<?php

namespace App\Service;

use App\Entity\Client;

class ClientContextBuilder
{
    /**
     * Monta um texto de contexto sobre o cliente para ser enviado ao GPT.
     *
     * Esta função coleta dados do cliente, projetos e históricos e os
     * organiza em um texto estruturado. Quanto mais rico o contexto,
     * melhores serão as respostas — mas mais tokens você vai gastar.
     *
     * A estratégia aqui é enviar apenas os campos mais relevantes e
     * limitar o tamanho das transcrições para economizar tokens.
     */

    public function __construct(
        private string $projectDir
    ) {
    }

    public function build(Client $client): string
    {
        $lines = [];

        // Adicionar a skill


        $filePath = $this->getParameter('kernel.project_dir') . '/assets/skill/prontuario-cliente.md';

        if (file_exists($filePath)) {
            $conteudo = file_get_contents($filePath);
        } else {
            throw new \Exception('Arquivo não encontrado: ' . $filePath);
        }
        $lines[] = $conteudo;

        // Informações básicas do cliente
        $lines[] = "=== DADOS DO CLIENTE ===";
        $lines[] = "Nome: " . ($client->getName() ?? 'N/A');
        $lines[] = "Nome Fantasia: " . ($client->getFantasyName() ?? 'N/A');
        $lines[] = "Documento: " . ($client->getDocument() ?? 'N/A');
        $lines[] = "E-mail: " . ($client->getMail() ?? 'N/A');
        $lines[] = "Telefone: " . ($client->getPhone1() ?? 'N/A');
        $lines[] = "Tipo: " . ($client->getType() ?? 'N/A');
        $lines[] = "Status: " . ($client->getStatus() ?? 'N/A');

        if ($client->getObs()) {
            $lines[] = "Observações: " . $client->getObs();
        }

        // Projetos do cliente
        $projects = $client->getClientProjects();
        if ($projects->count() > 0) {
            $lines[] = "";
            $lines[] = "=== PROJETOS (" . $projects->count() . " no total) ===";

            foreach ($projects as $project) {
                $lines[] = "";
                $lines[] = "--- Projeto: " . $project->getTitle() . " ---";

                if ($project->getDescription()) {
                    $lines[] = "Descrição: " . $project->getDescription();
                }
                if ($project->getFullDescription()) {
                    // Limita para não gastar muitos tokens
                    $lines[] = "Descrição Completa: " . mb_substr($project->getFullDescription(), 0, 500);
                }
                if ($project->getDateStart()) {
                    $lines[] = "Início: " . $project->getDateStart()->format('d/m/Y');
                }
                if ($project->getDateEnd()) {
                    $lines[] = "Término: " . $project->getDateEnd()->format('d/m/Y');
                }
                if ($project->getObs()) {
                    $lines[] = "Obs: " . mb_substr($project->getObs(), 0, 300);
                }

                // Históricos do projeto
                $histories = $project->getClientProjectHistories();
                if ($histories->count() > 0) {
                    $lines[] = "Históricos (" . $histories->count() . " registros):";

                    // Limita a 5 históricos mais recentes para economizar tokens
                    $historyCount = 0;
                    foreach ($histories as $history) {
                        if ($historyCount >= 5) {
                            $lines[] = "  ... (e mais " . ($histories->count() - 5) . " históricos omitidos)";
                            break;
                        }

                        if ($history->getOccurredAt()) {
                            $lines[] = "  [" . $history->getOccurredAt()->format('d/m/Y') . "]";
                        }
                        if ($history->getSummary()) {
                            $lines[] = "  Resumo: " . mb_substr($history->getSummary(), 0, 300);
                        }
                        if ($history->getTranscript()) {
                            // Transcrições podem ser longas — limitamos bastante
                            $lines[] = "  Transcrição (trecho): " . mb_substr($history->getTranscript(), 0, 500);
                        }

                        $historyCount++;
                    }
                }
            }
        } else {
            $lines[] = "";
            $lines[] = "Este cliente não possui projetos cadastrados.";
        }

        return implode("\n", $lines);
    }
}