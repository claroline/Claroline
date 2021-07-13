<?php

namespace Claroline\LogBundle\Entity\Archive;

use Claroline\LogBundle\Entity\AbstractLog;
use Claroline\LogBundle\Entity\FunctionalLog;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(null)
 */
class FunctionalLogArchive extends AbstractLog
{
    /**
     * @var string|null
     * @ORM\Column(type="int", nullable=true)
     */
    private $userId;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    private $userUuid;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    private $userUsername;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    private $workspaceUuid;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    private $resourceUuid;

    public static function fromFunctionalLog(FunctionalLog $functionalLog)
    {
        $user = $functionalLog->getUser();
        $workspace = $functionalLog->getWorkspace();
        $resource = $functionalLog->getResource();

        $archive = new self();
        $archive->setUserId($user->getId());
        $archive->setUserUuid($user->getUuid());
        $archive->setUserUsername($user->getUsername());
        $archive->setResourceUuid($resource ? $resource->getUuid() : null);
        $archive->setWorkspaceUuid($workspace ? $workspace->getUuid() : null);

        $archive->setDate($functionalLog->getDate());
        $archive->setDetails($functionalLog->getDetails());
        $archive->setEvent($functionalLog->getEvent());

        return $archive;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $userId): void
    {
        $this->userId = $userId;
    }

    public function getUserUuid(): ?string
    {
        return $this->userUuid;
    }

    public function setUserUuid(?string $userUuid): void
    {
        $this->userUuid = $userUuid;
    }

    public function getUserUsername(): ?string
    {
        return $this->userUsername;
    }

    public function setUserUsername(?string $userUsername): void
    {
        $this->userUsername = $userUsername;
    }

    public function getWorkspaceUuid(): ?string
    {
        return $this->workspaceUuid;
    }

    public function setWorkspaceUuid(?string $workspaceUuid): void
    {
        $this->workspaceUuid = $workspaceUuid;
    }

    public function getResourceUuid(): ?string
    {
        return $this->resourceUuid;
    }

    public function setResourceUuid(?string $resourceUuid): void
    {
        $this->resourceUuid = $resourceUuid;
    }
}
