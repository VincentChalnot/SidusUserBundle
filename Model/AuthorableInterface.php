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
/*
 * This file is part of the Sidus/UserBundle package.
 *
 * Copyright (c) 2021 Vincent Chalnot
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sidus\UserBundle\Model;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Entities implementing this interface will be automatically "filled" with the current user info.
 *
 * @author Vincent Chalnot <vincent@sidus.fr>
 */
interface AuthorableInterface
{
    public function getUpdatedBy(): ?UserInterface;

    public function setUpdatedBy(?UserInterface $user): void;

    public function getCreatedBy(): ?UserInterface;

    public function setCreatedBy(?UserInterface $user): void;
}
