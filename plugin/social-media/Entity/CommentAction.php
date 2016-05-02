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
 * @ORM\Table(name="icap__socialmedia_comment")
 * @ORM\Entity(repositoryClass="Icap\SocialmediaBundle\Repository\CommentActionRepository")
 * Class CommentAction
 */
class CommentAction extends ActionBase
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
