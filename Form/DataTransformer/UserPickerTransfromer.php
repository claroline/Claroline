<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form\DataTransformer;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\UserManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * @DI\Service("claroline.transformer.user_picker")
 */
class UserPickerTransfromer implements DataTransformerInterface
{
    private $userManager;

    /**
     * @DI\InjectParams({
     *     "userManager" = @DI\Inject("claroline.manager.user_manager")
     * })
     */
    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    public function transform($user)
    {
        if ($user instanceof User) {

            return $user->getId();
        }

        return "";
    }

    public function reverseTransform($userId)
    {
        if (!$userId) {

            return null;
        }
        $user = $this->userManager->getUserById($userId);

        if (is_null($user)) {

            throw new TransformationFailedException();
        }

        return $user;
    }
}
