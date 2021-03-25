<?php

namespace Claroline\SamlBundle\Security\Store;

use Claroline\SamlBundle\Entity\IdEntry;
use Doctrine\Persistence\ObjectManager;
use LightSaml\Provider\TimeProvider\TimeProviderInterface;
use LightSaml\Store\Id\IdStoreInterface;

class IdStore implements IdStoreInterface
{
    /** @var ObjectManager */
    private $manager;

    /** @var TimeProviderInterface */
    private $timeProvider;

    /**
     * IdStore constructor.
     */
    public function __construct(ObjectManager $manager, TimeProviderInterface $timeProvider)
    {
        $this->manager = $manager;
        $this->timeProvider = $timeProvider;
    }

    /**
     * @param string $entityId
     * @param string $id
     */
    public function set($entityId, $id, \DateTime $expiryTime)
    {
        $idEntry = $this->manager->find(IdEntry::class, [
            'entityId' => $entityId,
            'id' => $id,
        ]);

        if (empty($idEntry)) {
            $idEntry = new IdEntry();
        }

        $idEntry
            ->setEntityId($entityId)
            ->setId($id)
            ->setExpiryTime($expiryTime);

        $this->manager->persist($idEntry);
        $this->manager->flush();
    }

    /**
     * @param string $entityId
     * @param string $id
     *
     * @return bool
     */
    public function has($entityId, $id)
    {
        /** @var IdEntry $idEntry */
        $idEntry = $this->manager->find(IdEntry::class, [
            'entityId' => $entityId,
            'id' => $id,
        ]);

        if (empty($idEntry)) {
            return false;
        }
        if ($idEntry->getExpiryTime()->getTimestamp() < $this->timeProvider->getTimestamp()) {
            return false;
        }

        return true;
    }
}
