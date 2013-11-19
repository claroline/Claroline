<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Resource;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_link")
 */
class Link extends AbstractResource
{
    /**
     * @ORM\Column()
     */
    protected $url;

    /**
     * Returns the link url.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set the link url.
     *
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }
}
