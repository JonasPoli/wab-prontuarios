<?php

namespace App\Form;

use App\Entity\Projeto;
use App\Entity\Cliente;
use App\Entity\Usuario;
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
            ])
            ->add('nome', TextType::class, [
                'label' => 'Nome do projeto',
            ])
            ->add('descricao', TextareaType::class, [
                'label' => 'Descrição',
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
