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

use Sidus\UserBundle\Entity\Group;
use Sidus\UserBundle\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * User edition form.
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class UserType extends AbstractType
{
    public function __construct(
        protected TokenStorageInterface $tokenStorage,
        protected AuthorizationCheckerInterface $authorizationChecker
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'email',
                EmailType::class,
                [
                    'label' => 'sidus.admin.user.form.email.label',
                    'required' => true,
                    'empty_data' => '',
                ]
            );

        if ($this->getUser() && $this->authorizationChecker->isGranted('ROLE_ADMIN', $this->getUser())) {
            $builder
                ->add(
                    'enabled',
                    CheckboxType::class,
                    [
                        'label' => 'sidus.admin.user.form.enabled.label',
                    ]
                )
                ->add(
                    'rawRoles',
                    RoleHierarchyType::class,
                    [
                        'label' => 'sidus.admin.user.form.roles.label',
                    ]
                )
                ->add(
                    'groups',
                    EntityType::class,
                    [
                        'label' => 'sidus.admin.user.form.groups.label',
                        'class' => Group::class,
                        'expanded' => true,
                        'multiple' => true,
                    ]
                );
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => User::class,
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'eavmanager_user';
    }

    protected function getUser(): ?UserInterface
    {
        if (!$this->tokenStorage->getToken()) {
            return null;
        }

        return $this->tokenStorage->getToken()->getUser();
    }
}
