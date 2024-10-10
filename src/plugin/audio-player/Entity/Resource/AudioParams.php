<?php

namespace Claroline\AudioPlayerBundle\Entity\Resource;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Description;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_audio_params')]
#[ORM\Entity]
class AudioParams
{
    use Id;
    use Uuid;
    use Description;

    const MANAGER_TYPE = 'manager';
    const USER_TYPE = 'user';
    const NO_TYPE = 'none';

    #[ORM\JoinColumn(name: 'node_id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: \Claroline\CoreBundle\Entity\Resource\ResourceNode::class)]
    protected $resourceNode;

    #[ORM\Column(name: 'sections_type')]
    private $sectionsType = self::MANAGER_TYPE;

    #[ORM\Column(name: 'rate_control', type: 'boolean')]
    private $rateControl = true;

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function getResourceNode()
    {
        return $this->resourceNode;
    }

    public function setResourceNode(ResourceNode $resourceNode)
    {
        $this->resourceNode = $resourceNode;
    }

    public function getSectionsType()
    {
        return $this->sectionsType;
    }

    public function setSectionsType($sectionsType)
    {
        $this->sectionsType = $sectionsType;
    }

    public function getRateControl()
    {
        return $this->rateControl;
    }

    public function setRateControl($rateControl)
    {
        $this->rateControl = $rateControl;
    }
}
