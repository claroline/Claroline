<?php

namespace Claroline\AudioPlayerBundle\Entity\Resource;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Description;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_audio_params')]
#[ORM\Entity]
class AudioParams
{
    use Id;
    use Uuid;
    use Description;

    public const MANAGER_TYPE = 'manager';
    public const USER_TYPE = 'user';
    public const NO_TYPE = 'none';

    #[ORM\JoinColumn(name: 'node_id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: ResourceNode::class)]
    private ?ResourceNode $resourceNode = null;

    #[ORM\Column(name: 'sections_type')]
    private string $sectionsType = self::MANAGER_TYPE;

    #[ORM\Column(name: 'rate_control', type: Types::BOOLEAN)]
    private bool $rateControl = true;

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function getResourceNode(): ?ResourceNode
    {
        return $this->resourceNode;
    }

    public function setResourceNode(ResourceNode $resourceNode): void
    {
        $this->resourceNode = $resourceNode;
    }

    public function getSectionsType(): string
    {
        return $this->sectionsType;
    }

    public function setSectionsType(string $sectionsType): void
    {
        $this->sectionsType = $sectionsType;
    }

    public function getRateControl(): bool
    {
        return $this->rateControl;
    }

    public function setRateControl(bool $rateControl): void
    {
        $this->rateControl = $rateControl;
    }
}
