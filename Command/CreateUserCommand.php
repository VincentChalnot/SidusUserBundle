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
use Sidus\UserBundle\Exception\BadUsernameException;
use Sidus\UserBundle\Helper\UserManagementCommandHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Use this command to create users
 *
 * @author Vincent Chalnot <vincent@sidus.fr>
 */
class CreateUserCommand extends Command
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
            ->setName('sidus:user:create')
            ->setAliases(['user:create'])
            ->setDescription('Creates a user')
            ->addArgument('username', InputArgument::OPTIONAL, 'The username which is also the email')
            ->addOption(
                'password',
                'p',
                InputOption::VALUE_OPTIONAL,
                'The password, if omitted the user will receive an email with a random password'
            )
            ->addOption('admin', 'a', InputOption::VALUE_NONE, 'Set the user as super-admin')
            ->addOption('if-not-exists', null, InputOption::VALUE_NONE, 'Only if the user does not already exists');
    }

    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $username = $this->helper->getUsername($input, $output);
        if ($input->getOption('if-not-exists')) {
            try {
                $user = $this->userManager->loadUserByUsername($username);
            } catch (\Exception) {
                $user = null;
            }
            if ($user) {
                $output->writeln("<comment>User '{$username}' already exists</comment>");

                return static::SUCCESS;
            }
        }

        try {
            $user = $this->userManager->createUser($username);
        } catch (BadUsernameException $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");

            return static::FAILURE;
        }

        $user->setAdmin($input->getOption('admin'));
        $password = $this->helper->getPassword($input, $output);
        if ($password) {
            $this->userManager->setPlainTextPassword($user, $password);
        }
        $this->userManager->save($user);

        $output->writeln("<info>User {$user->getUsername()} was created successfully</info>");

        return static::SUCCESS;
    }
}
