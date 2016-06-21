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
use Doctrine\Common\Collections\Collection;

/**
 * @DI\Service("claroline.transformer.user_picker")
 */
class UserPickerTransfromer implements DataTransformerInterface
{
    private $userManager;
    private $options;

    /**
     * @DI\InjectParams({
     *     "userManager" = @DI\Inject("claroline.manager.user_manager")
     * })
     */
    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
        $this->options = array();
    }

    public function transform($value)
    {
        if (is_array($value) || $value instanceof Collection) {
            $transformedData = array();

            foreach ($value as $user) {
                $transformedData[] = array(
                    'id' => $user->getId(),
                    'name' => $user->getFirstName().' '.$user->getLastName(),
                );
            }

            return $transformedData;
        }

        if ($value instanceof User) {
            return array(
                'id' => $value->getId(),
                'name' => $value->getFirstName().' '.$value->getLastName(),
            );
        }

        return;
    }

    public function reverseTransform($userId)
    {
        if (empty($userId)) {
            return ($this->options['multiple']) ? array() : null;
        } elseif (strpos($userId, ',')) {
            $ids = explode(',', $userId);
            $users = $this->getByIds($ids);

            if (count($users) === 0) {
                return ($this->options['multiple']) ? array() : null;
            } else {
                return $users;
            }
        } else {
            $user = $this->userManager->getUserById($userId);
            if (is_null($user)) {
                throw new TransformationFailedException();
            } else {
                return ($this->options['multiple']) ? array($user) : $user;
            }
        }
    }

    private function getByIds($ids)
    {
        $users = array();

        foreach ($ids as $id) {
            $user = $this->userManager->getUserById(intval($id));
            if (is_null($user)) {
                throw new TransformationFailedException();
            } else {
                $users[] = $user;
            }
        }

        return $users;
    }

    public function setOptions($options)
    {
        $this->options = $options;
    }
}
