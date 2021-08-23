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

namespace Sidus\UserBundle\Command;

use Sidus\UserBundle\Domain\Manager\UserManagerInterface;
use Sidus\UserBundle\Helper\UserManagementCommandHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Promote users or remove their admin role
 *
 * @author Vincent Chalnot <vincent@sidus.fr>
 */
class PromoteUserCommand extends Command
{
    public function __construct(
        protected UserManagementCommandHelper $helper,
        protected UserManagerInterface $userManager,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('sidus:user:promote')
            ->setAliases(['user:promote'])
            ->setDescription('Promote a user to the admin role')
            ->addArgument('username', InputArgument::OPTIONAL, 'The username of the user')
            ->addOption('demote', 'd', InputOption::VALUE_NONE, 'Disable the admin role');
    }

    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $username = $this->helper->getUsername($input, $output);
        $user = $this->helper->findUser($output, $username);
        if (null === $user) {
            return static::FAILURE;
        }

        $user->setAdmin(!$input->getOption('demote'));
        $this->userManager->save($user);

        if ($user->isAdmin()) {
            $output->writeln("<info>User {$username} was promoted to admin</info>");
        } else {
            $output->writeln("<info>User {$username} was demoted from admin</info>");
        }

        return static::SUCCESS;
    }
}
