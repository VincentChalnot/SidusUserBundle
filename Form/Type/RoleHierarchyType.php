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

use Sidus\UserBundle\Security\Core\Role\LeafRole;
use Sidus\UserBundle\Security\Core\Role\RoleHierarchy;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Edit roles for users and groups.
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class RoleHierarchyType extends AbstractType
{
    public function __construct(protected RoleHierarchy $roleHierarchy)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $hierarchy = $options['hierarchy'];
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($hierarchy) {
                $roles = $event->getData();
                $form = $event->getForm();

                if ($hierarchy instanceof LeafRole) {
                    $options = [
                        'label' => $hierarchy->getRole(),
                        'required' => false,
                    ];
                    if (\is_array($roles)) {
                        /** @var array $roles */
                        foreach ($roles as $role) {
                            if ($role === $hierarchy->getRole()) {
                                unset($roles[$role]);
                                $options['data'] = true;
                            }
                        }
                    }
                    $form->add('hasRole', CheckboxType::class, $options);
                    $hierarchy = $hierarchy->getChildren();
                }

                foreach ($hierarchy as $subRole) {
                    $form->add(
                        $subRole->getRole(),
                        self::class,
                        [
                            'hierarchy' => $subRole,
                            'label' => false,
                            'data' => $roles,
                        ]
                    );
                }
            }
        );

        $builder->addModelTransformer(
            new CallbackTransformer(
                static function () {
                    // Delete original data:
                    return null;
                },
                static function ($submittedData) use ($hierarchy) {
                    if ($hierarchy instanceof LeafRole) {
                        if ($submittedData['hasRole']) {
                            $submittedData[] = $hierarchy->getRole();
                        }
                        unset($submittedData['hasRole']);
                    }
                    /** @var array $submittedData */
                    foreach ($submittedData as $key => $items) {
                        if (\is_array($items)) {
                            unset($submittedData[$key]);
                            /** @var array $items */
                            foreach ($items as $role) {
                                $submittedData[] = $role;
                            }
                        }
                    }

                    return $submittedData;
                }
            )
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'hierarchy' => $this->roleHierarchy->getTreeHierarchy(),
                'required' => false,
            ]
        );
        $resolver->setNormalizer(
            'hierarchy',
            function (Options $options, $value) {
                $error = "'hierarchy' option must be a LeafRole or an array of LeafRole";
                if (!$value instanceof \Traversable && !$value instanceof LeafRole) {
                    throw new \UnexpectedValueException($error);
                }
                if (\is_iterable($value)) {
                    /** @var array $value */
                    foreach ($value as $item) {
                        if (!$item instanceof LeafRole) {
                            throw new \UnexpectedValueException($error);
                        }
                    }
                }

                return $value;
            }
        );
    }
}
