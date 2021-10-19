<?php
declare(strict_types=1);

namespace Sidus\UserBundle\Model\Event;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Contracts\EventDispatcher\Event;

class MailEvent extends Event
{
    public function __construct(
        private TemplatedEmail $email,
    ) {
    }

    public function getEmail(): TemplatedEmail
    {
        return $this->email;
    }
}
