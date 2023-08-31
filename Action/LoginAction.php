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

namespace Sidus\UserBundle\Action;

use Sidus\UserBundle\Model\Configuration\UserConfiguration;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Twig\Environment;

/**
 * Login action with form
 */
#[AsController]
class LoginAction
{
    public function __construct(
        protected UserConfiguration $configuration,
        protected AuthenticationUtils $authenticationUtils,
        protected Security $security,
        protected Environment $twig,
        protected UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function __invoke(): RedirectResponse|array
    {
        if ($this->security->getUser()) {
            return new RedirectResponse($this->urlGenerator->generate($this->configuration->getHomeRoute()));
        }

        return [
            'last_username' => $this->authenticationUtils->getLastUsername(),
            'error' => $this->authenticationUtils->getLastAuthenticationError(),
        ];
    }
}
