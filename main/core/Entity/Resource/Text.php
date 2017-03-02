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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_text")
 */
class Text extends AbstractResource
{
    /**
     * @ORM\Column(type="integer")
     */
    protected $version;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\Revision",
     *     mappedBy="text",
     *     cascade={"persist"}
     * )
     * @ORM\OrderBy({"version" = "DESC"})
     */
    protected $revisions;

    /** @var string */
    protected $text;

    public function __construct()
    {
        $this->version = 1;
        $this->revisions = new ArrayCollection();
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function setVersion($version)
    {
        $this->version = $version;
    }

    public function getRevisions()
    {
        return $this->revisions;
    }

    public function addRevision($revision)
    {
        $this->revisions->add($revision);
    }

    public function removeRevision($revision)
    {
        $this->revisions->removeElement($revision);
    }

    /**
     * Required for the formtype.
     *
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * Required for the formtype.
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Get the current content of the Resource.
     *
     * @return string
     */
    public function getContent()
    {
        $content = null;
        if (!empty($this->revisions)) {
            $content = $this->revisions->get(0)->getContent();
        }

        return $content;
    }
}
