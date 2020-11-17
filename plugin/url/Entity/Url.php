<?php

namespace HeVinci\UrlBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\ORM\Mapping as ORM;
use HeVinci\UrlBundle\Model\Url as BaseUrl;
use HeVinci\UrlBundle\Model\UrlInterface;

/**
 * @ORM\Entity()
 * @ORM\Table(name="hevinci_url")
 */
class Url extends AbstractResource implements UrlInterface
{
    use BaseUrl;
}
