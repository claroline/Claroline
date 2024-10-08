<?php

namespace Claroline\CommunityBundle\Transfer\Importer\User;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\TransferBundle\Transfer\Importer\AbstractImporter;

final class Enable extends AbstractImporter
{
    public function __construct(
        private readonly ObjectManager $om,
        private readonly UserManager $userManager
    ) {
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
        return [static::getAction()[0] => User::class];
    }
}
