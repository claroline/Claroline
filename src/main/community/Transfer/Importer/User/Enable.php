<?php

namespace Claroline\CommunityBundle\Transfer\Importer\User;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\TransferBundle\Transfer\Importer\AbstractImporter;

class Enable extends AbstractImporter
{
    /** @var ObjectManager */
    private $om;
    /** @var UserManager */
    private $userManager;

    public function __construct(ObjectManager $om, UserManager $userManager)
    {
        $this->om = $om;
        $this->userManager = $userManager;
    }

    public function execute(array $data): array
    {
        /** @var User $object */
        $object = $this->om->getObject($data[static::getAction()[0]], User::class, array_keys($data[static::getAction()[0]]));

        if (!empty($object)) {
            $this->userManager->enable($object);

            return [
                'enable' => [[
                    'data' => $data,
                    'log' => static::getAction()[0].' enabled.',
                ]],
            ];
        }

        return [];
    }

    public static function getAction(): array
    {
        return ['user', 'enable'];
    }

    public function getSchema(?array $options = [], ?array $extra = []): array
    {
        //this is so we don't show all properties. See ImportProvider and search $root
        return [static::getAction()[0] => User::class];
    }
}
