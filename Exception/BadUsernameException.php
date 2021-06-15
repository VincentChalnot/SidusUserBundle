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

namespace Sidus\UserBundle\Exception;

use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Thrown at user creation when the username is not valid
 *
 * @author Vincent Chalnot <vincent@sidus.fr>
 */
class BadUsernameException extends \RuntimeException
{
    public static function createFromViolations(ConstraintViolationListInterface $constraintViolationList): self
    {
        $messages = [];
        /** @var ConstraintViolationInterface $violation */
        foreach ($constraintViolationList as $violation) {
            $messages[] = $violation->getMessage();
        }

        return new self(implode("\n", $messages));
    }
}
