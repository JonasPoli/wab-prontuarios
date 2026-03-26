<?php

namespace App\Form;

use App\Entity\Client;
use App\Entity\ClientProject;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\Image;



class ClientProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('fullDescription')
            ->add('dateEnd')
            ->add('dateStart')
            ->add('obs')
            ->add('createdAt', null, [
                'widget' => 'single_text',
            ])
            ->add('updatedAt', null, [
                'widget' => 'single_text',
            ])
            ->add('client', EntityType::class, [
                'class' => Client::class,
                'choice_label' => 'name',
            ]);

            // ->add('attached', FileType::class, [
            //     'label' => 'attached (PDF, JPEG, PNG file, JPG/PNG)',
            //     'mapped' => false,
            //     'required' => false,
            //     'multiple' => true,
            // ]);


       
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ClientProject::class,
        ]);
    }
}
