<?php

namespace App\Form;

use App\Entity\RegistroHistorico;
use App\Entity\RegistroHistoricoAnexo;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistroHistoricoAnexoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file')
            ->add('title')
            ->add('historicoAnexo', EntityType::class, [
                'class' => RegistroHistorico::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RegistroHistoricoAnexo::class,
        ]);
    }
}
