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

namespace Sidus\UserBundle\Action\Admin;

use Sidus\AdminBundle\Action\ActionInjectableInterface;
use Sidus\AdminBundle\Action\ActionInjectableTrait;
use Sidus\AdminBundle\Doctrine\DoctrineHelper;
use Sidus\AdminBundle\Form\FormHelper;
use Sidus\AdminBundle\Request\ActionResponseInterface;
use Sidus\AdminBundle\Request\RedirectActionResponse;
use Sidus\AdminBundle\Routing\RoutingHelper;
use Sidus\AdminBundle\Templating\TemplatingHelper;
use Sidus\UserBundle\Domain\Manager\UserManagerInterface;
use Sidus\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AsController]
#[IsGranted('ROLE_ADMIN')]
class ResetPasswordAction implements ActionInjectableInterface
{
    use ActionInjectableTrait;

    public function __construct(
        protected UserManagerInterface $userManager,
        protected FormHelper $formHelper,
        protected DoctrineHelper $doctrineHelper,
        protected RoutingHelper $routingHelper,
        protected TemplatingHelper $templatingHelper
    ) {
    }

    public function __invoke(Request $request, User $user): ActionResponseInterface
    {
        $form = $this->formHelper->getEmptyForm($this->action, $request);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->userManager->requestNewPassword($user);
            $this->doctrineHelper->addFlash($this->action, $request->getSession());

            return new RedirectActionResponse(
                action: $this->action->getAdmin()->getAction(
                    $this->action->getOption('redirect_action', 'list')
                )
            );
        }

        return $this->templatingHelper->renderFormAction(
            $this->action,
            $form,
            $user
        );
    }
}
