<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class CreateUserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'Login'
            ])
            ->add('roles', ChoiceType::class, [
                'required' => true,
                'multiple' => false,
                'expanded' => false,
                'choices'  => User::ROLES,
                'label' => 'Role'
            ])
            ->add('firstName', TextType::class, [
                'label' => 'Imię',
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nazwisko',
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => ['label' => 'Hasło'],
                'second_options' => ['label' => 'Powtórnie hasło'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Proszę wprowadzić hasło',
                    ]),
                    new Length([
                        'min' => 3,
                        'minMessage' => 'Twoje hasło musi mieć conajmniej {{ limit }} znaków',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Zapisz',
            ]);

        $builder->get('roles')
            ->addModelTransformer(new CallbackTransformer(
                function ($rolesArray) {
                    // transform the array to a string
                    return count($rolesArray) ? $rolesArray[0] : null;
                },
                function ($rolesString) {
                    // transform the string back to an array
                    return [$rolesString];
                }
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
