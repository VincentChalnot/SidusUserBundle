<?php
/*
 * This file is part of the Sidus/MediaBundle package.
 *
 * Copyright (c) 2021 Vincent Chalnot
 *
 * For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sidus\UserBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Vincent Chalnot <vincent@sidus.fr>
 */
class SidusUserBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig(
            'doctrine',
            [
                'orm' => [
                    'mappings' => [
                        $this->getName() => [
                            'mapping' => true,
                            'is_bundle' => true,
                            'type' => 'attribute',
                        ],
                    ],
                ],
            ]
        );
        $container->prependExtensionConfig(
            'sidus_template',
            [
                'routes' => [
                    'sidus.user.login' => [
                        'template' => '@SidusUser/Security/login.html.twig',
                    ],
                    'sidus.user.lost_password' => [
                        'template' => '@SidusUser/Security/lostPassword.html.twig',
                    ],
                    'sidus.user.reset_password' => [
                        'template' => '@SidusUser/Security/resetPassword.html.twig',
                    ],
                    'sidus.user.profile' => [
                        'template' => '@SidusUser/UserProfile/edit.html.twig',
                    ],
                ],
            ]
        );
    }
}
