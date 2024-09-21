<?php

namespace UJM\ExoBundle\Entity\Misc;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Library\Model\ContentTrait;
use UJM\ExoBundle\Library\Model\FeedbackTrait;
use UJM\ExoBundle\Library\Model\ScoreTrait;

#[ORM\MappedSuperclass]
abstract class AbstractChoice
{
    use Id;
    use ContentTrait;
    use FeedbackTrait;
    use ScoreTrait;
    use Uuid;

    public function __construct()
    {
        $this->refreshUuid();
    }
}
