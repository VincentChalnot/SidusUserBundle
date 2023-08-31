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

use Psr\Log\LoggerInterface;
use Sidus\UserBundle\Model\Configuration\UserConfiguration;
use Sidus\UserBundle\Domain\Manager\UserManagerInterface;
use Sidus\UserBundle\Form\Type\LostUserPasswordType;
use Sidus\UserBundle\Model\AdvancedUserInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Allow user to request a password reset
 */
#[AsController]
class LostPasswordAction
{
    public function __construct(
        protected UserManagerInterface $userManager,
        protected FormFactoryInterface $formFactory,
        protected TranslatorInterface $translator,
        protected UrlGeneratorInterface $urlGenerator,
        protected UserConfiguration $configuration,
        protected LoggerInterface $logger,
    ) {
    }

    public function __invoke(Request $request, ?UserInterface $user = null): Response|array
    {
        if ($user) {
            return new RedirectResponse($this->configuration->getHomeRoute());
        }
        $form = $this->formFactory->createNamed(
            'lost_password',
            LostUserPasswordType::class,
            null,
            [
                'attr' => [
                    'novalidate' => 'novalidate',
                ],
            ]
        );
        $form->handleRequest($request);

        $error = null;
        if ($form->isSubmitted() && $form->isValid()) {
            $user = null;
            try {
                $user = $this->userManager->findByUsername($form->get('email')->getData());
            } catch (UserNotFoundException) {
                $error = 'lost_password.not_found';
            } catch (\Exception $e) {
                $error = 'unknown_error';
                $this->logger->emergency($e->getMessage(), ['exception' => $e]);
            }

            if ($user instanceof AdvancedUserInterface) {
                $this->userManager->requestNewPassword($user);
                $session = $request->getSession();
                if ($session instanceof Session) {
                    $session->getFlashBag()->add(
                        'success',
                        $this->translator->trans('lost_password.password_changed', [], 'security')
                    );
                }

                return new RedirectResponse($this->urlGenerator->generate('sidus.user.login'));
            }
        }

        return [
            'form' => $form->createView(),
            'error' => $error,
        ];
    }
}
