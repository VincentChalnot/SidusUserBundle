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

namespace Sidus\UserBundle\Action\Profile;

use Sidus\UserBundle\Domain\Manager\UserManagerInterface;
use Sidus\UserBundle\Form\Type\UserPasswordType;
use Sidus\UserBundle\Model\AdvancedUserInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Expose a form to change user own's password
 */
#[AsController]
#[IsGranted('ROLE_USER')]
class ChangePasswordAction
{
    public function __construct(
        protected UserManagerInterface $userManager,
        protected FormFactoryInterface $formFactory,
        protected UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function __invoke(Request $request, UserInterface $user): Response|array
    {
        if (!$user instanceof AdvancedUserInterface) {
            throw new \UnexpectedValueException('User must be an AdvancedUserInterface');
        }
        $form = $this->formFactory->createNamed(
            'user_profile',
            UserPasswordType::class,
            [
                'email' => $user->getUserIdentifier(),
            ],
            [
                'label' => 'user.profile.title',
                'action' => $this->urlGenerator->generate('sidus.user.profile.change_password'),
                'attr' => [
                    'novalidate' => 'novalidate',
                ],
                'method' => 'post',
            ]
        );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->userManager->setPlainTextPassword($user, $form->getData()['password']);
            $this->userManager->save($user);

            $session = $request->getSession();
            if ($session instanceof Session) {
                $session->getFlashBag()->add('success', 'sidus.user.flash.edit.success');
            }

            return new RedirectResponse($this->urlGenerator->generate('sidus.user.profile.change_password'));
        }

        return [
            'form' => $form->createView(),
            'user' => $user,
        ];
    }
}
