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

namespace Sidus\UserBundle\Helper;

use Sidus\UserBundle\Model\AdvancedUserInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Sidus\UserBundle\Domain\Manager\UserManagerInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

/**
 * Common logic for user management
 */
class UserManagementCommandHelper
{
    protected QuestionHelper $questionHelper;

    public function __construct(
        protected UserManagerInterface $userManager,
    ) {
        $this->questionHelper = new QuestionHelper();
    }

    public function getUsername(InputInterface $input, OutputInterface $output): string
    {
        $username = $input->getArgument('username');
        if (null === $username) {
            if (!$input->isInteractive()) {
                throw new \UnexpectedValueException('Missing username argument');
            }
            $question = new Question(
                '<info>Username: </info>'
            );
            $username = $this->questionHelper->ask($input, $output, $question);
        }

        return $username;
    }

    public function findUser(OutputInterface $output, string $username): ?AdvancedUserInterface
    {
        try {
            /** @noinspection PhpIncompatibleReturnTypeInspection */
            return $this->userManager->loadUserByIdentifier($username);
        } catch (UserNotFoundException) {
            $output->writeln("<error>User '{$username}' does not exist</error>");

            return null;
        }
    }

    public function getPassword(InputInterface $input, OutputInterface $output): ?string
    {
        $password = $input->getOption('password');
        if (null === $password && $input->isInteractive()) {
            $output->writeln('<info>Leave password blank to generate a new password request</info>');
            $question = new Question(
                '<info>Password: </info>'
            );
            $question->setHidden(true);
            $password = (string) $this->questionHelper->ask($input, $output, $question);
        }

        return $password;
    }
}
