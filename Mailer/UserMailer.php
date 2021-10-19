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

use Psr\EventDispatcher\EventDispatcherInterface;
use Sidus\UserBundle\Model\AdvancedUserInterface;
use Sidus\UserBundle\Model\Configuration\UserConfiguration;
use Sidus\UserBundle\Model\Event\MailEvent;
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
        protected UserConfiguration $configuration,
        protected EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function sendNewUserMail(AdvancedUserInterface $user): void
    {
        $parameters = [
            'subject' => $this->translator->trans('user.account_creation', [], 'security'),
        ];

        $message = $this->createEmail($user, $parameters)
            ->textTemplate($this->configuration->getNewUserTextTemplate())
            ->htmlTemplate($this->configuration->getNewUserHtmlTemplate());

        $this->mailer->send($message);
    }

    public function sendResetPasswordMail(AdvancedUserInterface $user): void
    {
        $parameters = [
            'subject' => $this->translator->trans('user.reset_password', [], 'security'),
        ];

        $message = $this->createEmail($user, $parameters)
            ->textTemplate($this->configuration->getResetPasswordTextTemplate())
            ->htmlTemplate($this->configuration->getResetPasswordHtmlTemplate());

        $this->mailer->send($message);
    }

    protected function createEmail(AdvancedUserInterface $user, array $parameters): TemplatedEmail
    {
        $parameters = array_merge(
            [
                'homepage_route' => $this->configuration->getHomeRoute(),
                'user' => $user,
                'support' => $this->configuration->getSupportAddress(),
                'company' => $this->configuration->getCompanyTitle(),
            ],
            $parameters
        );

        $message = (new TemplatedEmail())
            ->context($parameters)
            ->from($this->configuration->getMailerFrom())
            ->subject($parameters['subject'])
            ->addTo($user->getEmail());

        $event = new MailEvent($message);
        $this->eventDispatcher->dispatch($event);

        return $message;
    }
}
