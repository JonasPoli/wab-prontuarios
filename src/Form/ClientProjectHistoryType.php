<?php

namespace App\Form;

use App\Entity\ClientProject;
use App\Entity\ClientProjectHistory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Repository\ClientProjectRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;


class ClientProjectHistoryType extends AbstractType
{
public function buildForm(FormBuilderInterface $builder, array $options): void
{
    $builder
        ->add('occurredAt', null, [
            'widget' => 'single_text',
        ])
        ->add('summary')
        ->add('transcript')
        ->add('clientProject', EntityType::class, [
            'class' => ClientProject::class,
            'query_builder' => function (ClientProjectRepository $repository): QueryBuilder {
                return $repository->createQueryBuilder('p')
                    ->join('p.client', 'c')
                    ->orderBy('p.title', 'ASC')
                    ->orderBy('c.name', 'ASC');
            },
            'choice_label' => function (ClientProject $project): string {
                return $project->getClient()->getName() . ' - ' . $project->getTitle();
            },
        ])
        ->add('audioFile', FileType::class, [
            'label' => 'Arquivo de Áudio',
            'mapped' => false,
            'required' => false,
            'constraints' => [
                new File(
                    maxSize: '20M',
                    mimeTypes: [
                        'audio/mpeg',
                        'audio/mp3',
                        'audio/mp4',
                        'audio/x-m4a',
                        'audio/aac',
                    ],
                    mimeTypesMessage: 'Por favor, envie um arquivo de áudio válido (MP3 ou M4A).',
            ),
            ],
        ]);
    }
    

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ClientProjectHistory::class,
        ]);
    }
}

