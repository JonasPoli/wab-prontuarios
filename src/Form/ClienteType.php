<?php

namespace App\Form;

use App\Entity\Cliente;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ClienteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('tipo', ChoiceType::class, [
                'choices' => [
                    'Pessoa Física' => 'pessoa_fisica',
                    'Pessoa Jurídica' => 'pessoa_juridica',
                ],
                'label' => 'Tipo de cliente',
            ])

            ->add('nome', TextType::class, [
                'label' => 'Nome / Razão Social',
            ])

            ->add('apelidoFantasia', TextType::class, [
                'label' => 'Apelido / Nome Fantasia',
                'required' => false,
            ])

            ->add('documento', TextType::class, [
                'label' => 'CPF / CNPJ',
                'required' => false,
            ])

            ->add('email', EmailType::class, [
                'label' => 'E-mail',
                'required' => false,
            ])

            ->add('telefonePrincipal', TextType::class, [
                'label' => 'Telefone principal',
                'required' => false,
            ])

            ->add('telefoneSecundario', TextType::class, [
                'label' => 'Telefone secundário',
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
            'data_class' => Cliente::class,
        ]);
    }
}
