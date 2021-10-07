<?php
/*
 * This file is part of the Sidus/UserBundle package.
 *
 * Copyright (c) 2021 Vincent Chalnot
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sidus\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

/**
 * User password edition
 *
 * @author Vincent Chalnot <vincent@sidus.fr>
 */
class UserPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'email',
                EmailType::class,
                [
                    'attr' => [
                        'readonly' => 'readonly',
                    ],
                ],
            )
            ->add(
                'currentPassword',
                PasswordType::class,
                [
                    'mapped' => false,
                    'constraints' => [
                        new UserPassword(),
                    ],
                ],
            )
            ->add(
                'password',
                RepeatedType::class,
                [
                    'type' => PasswordType::class,
                    'required' => true,
                    'first_name' => 'password',
                    'second_name' => 'confirmPassword',
                    'first_options' => ['attr' => ['autocomplete' => 'new-password']],
                ],
            );
    }
}
