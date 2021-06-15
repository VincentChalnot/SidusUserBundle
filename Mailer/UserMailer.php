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

namespace Sidus\UserBundle\Mailer;

use Sidus\UserBundle\Model\Configuration\UserConfiguration;
use Sidus\UserBundle\Model\AdvancedUserInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * Handles mailing to users for security steps (account creation, password reset).
 *
 * @author Vincent Chalnot <vincent@sidus.fr>
 */
class UserMailer
{
    public function __construct(
        protected MailerInterface $mailer,
        protected Environment $twig,
        protected TranslatorInterface $translator,
        protected UserConfiguration $configuration
    ) {
    }

    public function sendNewUserMail(AdvancedUserInterface $user): void
    {
        $parameters = [
            'homepage_route' => $this->configuration->getHomeRoute(),
            'user' => $user,
            'support' => $this->configuration->getSupportAddress(),
            'subject' => $this->translator->trans('user.account_creation', [], 'security'),
            'company' => $this->configuration->getCompanyTitle(),
        ];

        $message = (new TemplatedEmail())
            ->textTemplate($this->configuration->getNewUserTextTemplate())
            ->htmlTemplate($this->configuration->getNewUserHtmlTemplate())
            ->context($parameters)
            ->from($this->configuration->getMailerFrom())
            ->subject($parameters['subject'])
            ->addTo($user->getEmail());

        $this->mailer->send($message);
    }

    public function sendResetPasswordMail(AdvancedUserInterface $user): void
    {
        $parameters = [
            'homepage_route' => $this->configuration->getHomeRoute(),
            'user' => $user,
            'support' => $this->configuration->getSupportAddress(),
            'subject' => $this->translator->trans('user.reset_password', [], 'security'),
            'company' => $this->configuration->getCompanyTitle(),
        ];

        $message = (new TemplatedEmail())
            ->textTemplate($this->configuration->getResetPasswordTextTemplate())
            ->htmlTemplate($this->configuration->getResetPasswordHtmlTemplate())
            ->context($parameters)
            ->from($this->configuration->getMailerFrom())
            ->subject($parameters['subject'])
            ->addTo($user->getEmail());

        $this->mailer->send($message);
    }
}
