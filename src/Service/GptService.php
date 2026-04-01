<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GptService
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $apiKey,
        private readonly string $model = 'gpt-4o-mini'
    ) {}

    /**
     * Envia uma pergunta para o GPT e retorna a resposta.
     *
     * @param string $systemPrompt  Instruções iniciais para o GPT (o contexto do cliente)
     * @param array  $messages      Histórico de mensagens no formato [{role, content}, ...]
     * @return array ['content' => string, 'tokens_used' => int]
     */
    public function chat(string $systemPrompt, array $messages): array
    {
        // Monta o array de mensagens no formato que a API do OpenAI espera
        $apiMessages = [
            ['role' => 'system', 'content' => $systemPrompt],
        ];

        // Adiciona o histórico de mensagens anteriores
        foreach ($messages as $msg) {
            $apiMessages[] = [
                'role'    => $msg['role'],    // 'user' ou 'assistant'
                'content' => $msg['content'],
            ];
        }

        // Faz a chamada HTTP para a API da OpenAI
        $response = $this->httpClient->request('POST', 'https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type'  => 'application/json',
            ],
            'json' => [
                'model'       => $this->model,
                'messages'    => $apiMessages,
                'max_tokens'  => 1000,      // Limita o tamanho da resposta
                'temperature' => 0.7,       // Controla a criatividade (0=conservador, 1=criativo)
            ],
        ]);

        $data = $response->toArray();

        return [
            'content'     => $data['choices'][0]['message']['content'] ?? 'Não foi possível obter resposta.',
            'tokens_used' => $data['usage']['total_tokens'] ?? 0,
        ];
    }
}