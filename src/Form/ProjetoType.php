<?php

namespace App\Form;

use App\Entity\Projeto;
use App\Entity\Cliente;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjetoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('cliente', EntityType::class, [
                'class' => Cliente::class,
                'choice_label' => 'nome',
                'label' => 'Cliente',
                'placeholder' => 'Selecione um cliente',
            ])
            ->add('titulo', TextType::class, [
                'label' => 'Título do projeto',
            ])
            ->add('descricaoResumida', TextareaType::class, [
                'label' => 'Descrição resumida',
                'required' => false,
            ])
            ->add('descricaoDetalhada', TextareaType::class, [
                'label' => 'Descrição detalhada',
                'required' => false,
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Status',
                'choices' => [
                    'Planejado' => 'planejado',
                    'Em andamento' => 'em_andamento',
                    'Concluído' => 'concluido',
                    'Cancelado' => 'cancelado',
                ],
            ])
            ->add('dataInicioPrevista', DateType::class, [
                'label' => 'Data início prevista',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('dataFimPrevista', DateType::class, [
                'label' => 'Data fim prevista',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('observacoes', TextareaType::class, [
                'label' => 'Observações',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Projeto::class,
        ]);
    }
}
