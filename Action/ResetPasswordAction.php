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
use Sidus\UserBundle\Domain\Manager\UserManagerInterface;
use Sidus\UserBundle\Form\Type\ResetUserPasswordType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Expose a form to reset the password of user when login from a token link
 */
#[AsController]
class ResetPasswordAction
{
    public function __construct(
        protected UserManagerInterface $userManager,
        protected FormFactoryInterface $formFactory,
        protected TranslatorInterface $translator,
        protected UrlGeneratorInterface $urlGenerator,
        protected UserConfiguration $configuration,
    ) {
    }

    public function __invoke(Request $request, ?UserInterface $user = null): Response|array
    {
        if ($user) {
            $this->addFlashMessage(
                $request,
                'error',
                $this->translator->trans('reset_password.already_connected', [], 'security')
            );

            return new RedirectResponse($this->urlGenerator->generate($this->configuration->getHomeRoute()));
        }
        $token = $request->query->get('token');
        if (!$token) {
            $this->addFlashMessage(
                $request,
                'error',
                $this->translator->trans('reset_password.token_not_found', [], 'security')
            );

            return new RedirectResponse($this->urlGenerator->generate('sidus.user.login'));
        }

        $user = $this->userManager->loadUserByToken($token);
        if (!$user) {
            $this->addFlashMessage(
                $request,
                'error',
                $this->translator->trans('reset_password.token_not_found', [], 'security')
            );

            return new RedirectResponse($this->urlGenerator->generate('sidus.user.lost_password'));
        }

        $form = $this->formFactory->createNamed('reset_password', ResetUserPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $form->get('password')->getData();
            $this->userManager->setPlainTextPassword($user, $password);
            $this->userManager->save($user);

            $this->addFlashMessage(
                $request,
                'success',
                $this->translator->trans('reset_password.success', [], 'security')
            );

            return new RedirectResponse($this->urlGenerator->generate('sidus.user.login'));
        }

        return [
            'user' => $user,
            'form' => $form->createView(),
        ];
    }

    protected function addFlashMessage(Request $request, string $type, string $message): void
    {
        $session = $request->getSession();
        if ($session instanceof Session) {
            $session->getFlashBag()->add($type, $message);
        }
    }
}
