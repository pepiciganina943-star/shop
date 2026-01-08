<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class CheckoutType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fullName', TextType::class, [
                'label' => 'Име и Фамилия',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Иван Иванов'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Моля, въведете вашето име']),
                    new Assert\Length([
                        'min' => 3,
                        'minMessage' => 'Името трябва да е поне {{ limit }} символа',
                    ]),
                ],
            ])
            ->add('phone', TelType::class, [
                'label' => 'Телефонен номер',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '+359 888 123 456'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Моля, въведете вашия телефон']),
                    new Assert\Regex([
                        'pattern' => '/^[\+]?[0-9\s\-\(\)]+$/',
                        'message' => 'Невалиден телефонен номер',
                    ]),
                ],
            ])
            ->add('address', TextareaType::class, [
                'label' => 'Адрес за доставка',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Град, улица, номер, етаж, апартамент...'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Моля, въведете адрес за доставка']),
                    new Assert\Length([
                        'min' => 10,
                        'minMessage' => 'Адресът трябва да е поне {{ limit }} символа',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
