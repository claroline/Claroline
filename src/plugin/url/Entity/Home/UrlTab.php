<?php

namespace HeVinci\UrlBundle\Entity\Home;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\HomeBundle\Entity\Type\AbstractTab;
use Doctrine\ORM\Mapping as ORM;
use HeVinci\UrlBundle\Model\Url;
use HeVinci\UrlBundle\Model\UrlInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_home_tab_url")
 */
class UrlTab extends AbstractTab implements UrlInterface
{
    use Id;
    // URL config
    use Url;

    public static function getType(): string
    {
        return 'url';
    }
}
