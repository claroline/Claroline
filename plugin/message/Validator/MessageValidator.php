<?php

namespace Claroline\MessageBundle\Validator;

use Claroline\AppBundle\API\ValidatorInterface;
use Claroline\AppBundle\API\ValidatorProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\MessageBundle\Entity\Message;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service()
 * @DI\Tag("claroline.validator")
 */
class MessageValidator implements ValidatorInterface
{
    /**
     * GroupValidator constructor.
     *
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    public function validate($data, $mode)
    {
        $errors = [];

        if (ValidatorProvider::UPDATE === $mode) {
            $object = $this->om->getRepository($this->getClass())->find($data['id']);

            if (!$object) {
                return [];
            }

            if (isset($data['content']) && $object->getContent() !== $data['content']) {
                $errors[] = [
                    'path' => 'content',
                    'message' => 'The content can not be changed.',
                ];
            }

            if (isset($data['object']) && $object->getObject() !== $data['object']) {
                $errors[] = [
                    'path' => 'object',
                    'message' => 'The object can not be changed.',
                ];
            }

            return $errors;
        }
        if (isset($data['to'])) {
            $error = $this->validateTo($data['to']);
        }

        if ($error) {
            $errors[] = ['path' => 'to', 'message' => $error];
        }

        return $errors;
    }

    private function validateTo($to)
    {
        $error = null;

        $to = trim($to);

        if (';' === substr($to, -1, 1)) {
            $to = substr_replace($to, '', -1);
        }

        $names = explode(';', $to);
        $usernames = [];
        $groupNames = [];
        $workspaceCodes = [];
        //split the string of target into different array.
        foreach ($names as $name) {
            if ('{' === substr($name, 0, 1)) {
                $groupNames[] = trim($name, '{}');
            } else {
                if ('[' === substr($name, 0, 1)) {
                    $workspaceCodes[] = trim($name, '[]');
                } else {
                    $usernames[] = trim($name);
                }
            }
        }

        foreach ($usernames as $username) {
            $user = $this->om->getRepository(User::class)->findOneBy(['username' => $username]);
            if (null === $user) {
                if (!$error) {
                    $error = 'User '.$username.' not found.';
                } else {
                    $error .= '\nUser '.$username.' not found.';
                }
            }
        }

        foreach ($groupNames as $groupName) {
            $group = $this->om->getRepository(Group::class)->findOneBy(['name' => $groupName]);
            if (null === $group) {
                if (!$error) {
                    $error = 'Group '.$groupName.' not found.';
                } else {
                    $error .= '\nGroup '.$groupName.' not found.';
                }
            }
        }

        foreach ($workspaceCodes as $workspaceCode) {
            $ws = $this->om->getRepository(Workspace::class)->findOneBy(['code' => $workspaceCode]);
            if (null === $ws) {
                if (!$error) {
                    $error = 'Workspace '.$workspaceCode.' not found.';
                } else {
                    $error .= '\nWorkspace '.$workspaceCode.' not found.';
                }
            }
        }

        return $error;
    }

    public function getClass()
    {
        return Message::class;
    }

    public function getUniqueFields()
    {
        return [];
    }
}
