<?php
declare(strict_types=1);
/*
 * This file is part of the Sidus/UserBundle package.
 *
 * Copyright (c) 2021 Vincent Chalnot
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sidus\UserBundle\Model\Configuration;

use Symfony\Component\Mime\Address;

/**
 * Handles the configuration of the user management system.
 *
 * @author Vincent Chalnot <vincent@sidus.fr>
 */
class UserConfiguration
{
    protected string $companyTitle;
    protected Address $supportAddress;
    protected Address $mailerFrom;
    protected string $homeRoute;
    protected string $newUserHtmlTemplate;
    protected string $newUserTextTemplate;
    protected string $resetPasswordHtmlTemplate;
    protected string $resetPasswordTextTemplate;

    public function __construct(array $config)
    {
        $this->companyTitle = $config['company_title'];
        $this->supportAddress = new Address($config['mailer']['support_email'], $config['mailer']['support_name']);
        $this->mailerFrom = new Address($config['mailer']['from_email'], $config['mailer']['from_name']);
        $this->homeRoute = $config['home_route'];
        $this->newUserHtmlTemplate = $config['templates']['new_user']['html'];
        $this->newUserTextTemplate = $config['templates']['new_user']['text'];
        $this->resetPasswordHtmlTemplate = $config['templates']['reset_password']['html'];
        $this->resetPasswordTextTemplate = $config['templates']['reset_password']['text'];
    }

    public function getCompanyTitle(): string
    {
        return $this->companyTitle;
    }

    public function getSupportAddress(): Address
    {
        return $this->supportAddress;
    }

    public function getMailerFrom(): Address
    {
        return $this->mailerFrom;
    }

    public function getHomeRoute(): string
    {
        return $this->homeRoute;
    }

    public function getNewUserHtmlTemplate(): string
    {
        return $this->newUserHtmlTemplate;
    }

    public function getNewUserTextTemplate(): string
    {
        return $this->newUserTextTemplate;
    }

    public function getResetPasswordHtmlTemplate(): string
    {
        return $this->resetPasswordHtmlTemplate;
    }

    public function getResetPasswordTextTemplate(): string
    {
        return $this->resetPasswordTextTemplate;
    }
}
