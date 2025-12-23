<?php

namespace App\Form;

use App\Entity\Cliente;
use App\Entity\Projeto;
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
                'label' => 'Título do projeto',
                'required' => true,
            ])

            ->add('codigoInterno', TextType::class, [
                'label' => 'Código interno',
                'required' => false,
            ])

            ->add('descricaoResumida', TextareaType::class, [
                'label' => 'Descrição resumida',
                'required' => false,
                'attr' => [
                    'rows' => 3,
                ],
            ])

            ->add('descricaoDetalhada', TextareaType::class, [
                'label' => 'Descrição detalhada',
                'required' => false,
                'attr' => [
                    'rows' => 5,
                ],
            ])

            ->add('status', ChoiceType::class, [
                'required' => true,
                'choices' => [
                    'Planejado' => 'planejado',
                    'Em andamento' => 'em_andamento',
                    'Concluído' => 'concluido',
                    'Cancelado' => 'cancelado',
                ],
            ])

            ->add('dataInicioPrevista', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
                'label' => 'Início previsto',
            ])

            ->add('dataFimPrevista', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
                'label' => 'Fim previsto',
            ])

            ->add('dataInicioReal', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
                'label' => 'Início real',
            ])

            ->add('dataFimReal', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
                'label' => 'Fim real',
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
                'attr' => [
                    'rows' => 4,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Projeto::class,
        ]);
    }
}
