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
 * Use this command to change a user password
 */
class ChangeUserPasswordCommand extends Command
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
            ->setName('sidus:user:change-password')
            ->setAliases(['user:change-password'])
            ->setDescription('Change the password of a user')
            ->addArgument('username', InputArgument::OPTIONAL, 'The username of the user')
            ->addOption('password', 'p', InputOption::VALUE_REQUIRED, 'The new password of the user');
    }

    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $username = $this->helper->getUsername($input, $output);
        $user = $this->helper->findUser($output, $username);
        if (null === $user) {
            return static::FAILURE;
        }

        $password = $this->helper->getPassword($input, $output);
        if ($password) {
            $this->userManager->setPlainTextPassword($user, $password);
        } else {
            $this->userManager->requestNewPassword($user);
        }
        $this->userManager->save($user);

        if ($password) {
            $output->writeln('<info>Password changed successfully</info>');
        } else {
            $output->writeln('<info>New password request sent</info>');
        }

        return static::SUCCESS;
    }
}
