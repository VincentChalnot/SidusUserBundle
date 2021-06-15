<?php /** @noinspection NullPointerExceptionInspection */
/*
 * This file is part of the Sidus/MediaBundle package.
 *
 * Copyright (c) 2021 Vincent Chalnot
 *
 * For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sidus\UserBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('sidus_user');

        /** @formatter:off */
        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('home_route')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('company_title')->isRequired()->end()
                ->arrayNode('mailer')
                    ->isRequired()
                    ->children()
                        ->scalarNode('from_email')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('from_name')->isRequired()->end()
                       ->scalarNode('support_email')->isRequired()->cannotBeEmpty()->end()
                       ->scalarNode('support_name')->isRequired()->end()
                    ->end()
                ->end()
                ->arrayNode('templates')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('new_user')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('html')
                                    ->isRequired()
                                    ->defaultValue('@SidusUser/Email/newUser.html.twig')
                                ->end()
                                ->scalarNode('text')
                                    ->isRequired()
                                    ->defaultValue('@SidusUser/Email/newUser.txt.twig')
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('reset_password')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('html')
                                    ->isRequired()
                                    ->defaultValue('@SidusUser/Email/resetPassword.html.twig')
                                ->end()
                                ->scalarNode('text')
                                    ->isRequired()
                                    ->defaultValue('@SidusUser/Email/resetPassword.txt.twig')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
        /** @formatter:on */

        return $treeBuilder;
    }
}
