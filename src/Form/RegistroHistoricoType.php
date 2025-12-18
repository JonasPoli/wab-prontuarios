<?php

namespace App\Form;

use App\Entity\RegistroHistorico;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistroHistoricoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dataRegistro', DateTimeType::class, [
                'label' => 'Data do registro',
                'widget' => 'single_text',
            ])
            ->add('tipoRegistro', ChoiceType::class, [
                'label' => 'Tipo do registro',
                'required' => false,
                'choices' => [
                    'Anotação' => 'anotacao',
                    'Reunião' => 'reuniao',
                    'Ligação' => 'ligacao',
                    'E-mail' => 'email',
                    'Documento' => 'documento',
                ],
                'placeholder' => 'Selecione o tipo',
            ])
            ->add('titulo', TextType::class, [
                'label' => 'Título',
                'required' => false,
            ])
            ->add('descricao', TextareaType::class, [
                'label' => 'Descrição',
            ])
            ->add('visivelParaCliente', CheckboxType::class, [
                'label' => 'Visível para o cliente',
                'required' => false,
            ])
            ->add('tags', TextType::class, [
                'label' => 'Tags',
                'required' => false,
                'help' => 'Separe por vírgula',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RegistroHistorico::class,
        ]);
    }
}
