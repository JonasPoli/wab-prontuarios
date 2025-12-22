<?php

namespace App\Form;

use App\Entity\Projeto;
use App\Entity\Cliente;
use App\Entity\User;
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
    'placeholder' => 'Selecione o cliente',
    'required' => true,
])

        
            ->add('titulo', TextType::class, [
                'required' => true,
            ])

            ->add('codigoInterno', TextType::class, [
                'required' => false,
                'label' => 'Código interno',
            ])

            ->add('descricaoResumida', TextareaType::class, [
                'required' => false,
            ])

            ->add('descricaoDetalhada', TextareaType::class, [
                'required' => false,
            ])

            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Planejado' => 'planejado',
                    'Em andamento' => 'em_andamento',
                    'Concluído' => 'concluido',
                    'Cancelado' => 'cancelado',
                ],
                'required' => true,
            ])

            ->add('dataInicioPrevista', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])

            ->add('dataFimPrevista', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])

            ->add('dataInicioReal', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])

            ->add('dataFimReal', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])

            ->add('responsavelInterno', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'nomeCompleto',
                'placeholder' => 'Selecione o responsável',
                'required' => false,
            ])

            ->add('tags', TextType::class, [
                'required' => false,
            ])

            ->add('observacoes', TextareaType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Projeto::class,
        ]);
    }
}
