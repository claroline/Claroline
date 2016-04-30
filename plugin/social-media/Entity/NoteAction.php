<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 * 
 * Date: 4/23/15
 */

namespace Icap\SocialmediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="icap__socialmedia_note")
 * @ORM\Entity(repositoryClass="Icap\SocialmediaBundle\Repository\NoteActionRepository")
 * Class NoteAction
 */
class NoteAction extends ActionBase
{
    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $text = null;

    /**
     * @param string $text
     *
     * @return $this
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }
}
