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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sidus\AdminBundle\Action\ActionInjectableInterface;
use Sidus\AdminBundle\Action\ActionInjectableTrait;
use Sidus\AdminBundle\Doctrine\DoctrineHelper;
use Sidus\AdminBundle\Form\FormHelper;
use Sidus\AdminBundle\Routing\RoutingHelper;
use Sidus\AdminBundle\Templating\TemplatingHelper;
use Sidus\UserBundle\Domain\Manager\UserManagerInterface;
use Sidus\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Security("is_granted('create', _admin.getEntity())")
 */
class CreateAction implements ActionInjectableInterface
{
    use ActionInjectableTrait;

    public function __construct(
        protected FormHelper $formHelper,
        protected UserManagerInterface $userManager,
        protected DoctrineHelper $doctrineHelper,
        protected RoutingHelper $routingHelper,
        protected TemplatingHelper $templatingHelper,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $form = $this->formHelper->getForm($this->action, $request, new User(''));

        $form->handleRequest($request);
        $data = $form->getData();
        if ($form->isSubmitted() && $form->isValid()) {
            $this->userManager->save($data);
            $this->doctrineHelper->addFlash($this->action, $request->getSession());

            return $this->routingHelper->redirectToEntity(
                $this->action->getAdmin()->getAction(
                    $this->action->getOption('redirect_action', 'edit')
                ),
                $data,
                $request->query->all()
            );
        }

        return $this->templatingHelper->renderFormAction($this->action, $form, $data);
    }
}
