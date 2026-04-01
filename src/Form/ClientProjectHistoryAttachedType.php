<?php

namespace App\Form;

use App\Entity\ClientProjectHistoryAttached;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientProjectHistoryAttachedType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file', FileType::class, [
                'label' => 'Anexos',
                'mapped' => false,
                'required' => false,
                'multiple' => true,
                'attr' => [
                    'accept' => '.jpg,.jpeg,.png,.pdf',
                ],
            ])
            ->add('description', null, [
                'label' => 'Descrição',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ClientProjectHistoryAttached::class,
        ]);
    }
}