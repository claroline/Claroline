<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AudioPlayerBundle\Entity\Resource;

use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_audio_resource_section")
 */
class Section
{
    use Uuid;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode")
     * @ORM\JoinColumn(name="node_id", nullable=false, onDelete="CASCADE")
     */
    protected $resourceNode;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", nullable=true, onDelete="SET NULL")
     */
    protected $user;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $title;

    /**
     * @ORM\Column(name="section_start", type="float", nullable=false)
     */
    private $start;

    /**
     * @ORM\Column(name="section_end", type="float", nullable=false)
     */
    private $end;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $color;

    /**
     * @ORM\Column(name="section_type")
     */
    private $type;

    /**
     * @ORM\Column(name="show_transcript", type="boolean")
     */
    private $showTranscript = false;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $transcript;

    /**
     * @ORM\Column(name="comments_allowed", type="boolean")
     */
    private $commentsAllowed = false;

    /**
     * @ORM\Column(name="show_help", type="boolean")
     */
    private $showHelp = false;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $help;

    /**
     * @ORM\Column(name="show_audio", type="boolean")
     */
    private $showAudio = false;

    /**
     * @ORM\Column(name="audio_url", type="string", nullable=true)
     */
    private $audioUrl;

    /**
     * @ORM\Column(name="audio_description", type="string", nullable=true)
     */
    private $audioDescription;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\AudioPlayerBundle\Entity\Resource\SectionComment",
     *     mappedBy="section"
     * )
     * @ORM\OrderBy({"creationDate" = "DESC"})
     */
    protected $comments;

    public function __construct()
    {
        $this->refreshUuid();
        $this->comments = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return ResourceNode
     */
    public function getResourceNode()
    {
        return $this->resourceNode;
    }

    /**
     * @param ResourceNode $resourceNode
     */
    public function setResourceNode(ResourceNode $resourceNode)
    {
        $this->resourceNode = $resourceNode;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return float
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @param float $start
     */
    public function setStart($start)
    {
        $this->start = $start;
    }

    /**
     * @return float
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @param float $end
     */
    public function setEnd($end)
    {
        $this->end = $end;
    }

    /**
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param string $color
     */
    public function setColor($color)
    {
        $this->color = $color;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return bool
     */
    public function getShowTranscript()
    {
        return $this->showTranscript;
    }

    /**
     * @param bool $showTranscript
     */
    public function setShowTranscript($showTranscript)
    {
        $this->showTranscript = $showTranscript;
    }

    /**
     * @return string
     */
    public function getTranscript()
    {
        return $this->transcript;
    }

    /**
     * @param string $transcript
     */
    public function setTranscript($transcript)
    {
        $this->transcript = $transcript;
    }

    /**
     * @return bool
     */
    public function isCommentsAllowed()
    {
        return $this->commentsAllowed;
    }

    /**
     * @param bool $commentsAllowed
     */
    public function setCommentsAllowed($commentsAllowed)
    {
        $this->commentsAllowed = $commentsAllowed;
    }

    /**
     * @return bool
     */
    public function getShowHelp()
    {
        return $this->showHelp;
    }

    /**
     * @param bool $showHelp
     */
    public function setShowHelp($showHelp)
    {
        $this->showHelp = $showHelp;
    }

    /**
     * @return string
     */
    public function getHelp()
    {
        return $this->help;
    }

    /**
     * @param string $help
     */
    public function setHelp($help)
    {
        $this->help = $help;
    }

    /**
     * @return bool
     */
    public function getShowAudio()
    {
        return $this->showAudio;
    }

    /**
     * @param bool $showAudio
     */
    public function setShowAudio($showAudio)
    {
        $this->showAudio = $showAudio;
    }

    /**
     * @return string
     */
    public function getAudioUrl()
    {
        return $this->audioUrl;
    }

    /**
     * @param string $audioUrl
     */
    public function setAudioUrl($audioUrl)
    {
        $this->audioUrl = $audioUrl;
    }

    /**
     * @return string
     */
    public function getAudioDescription()
    {
        return $this->audioDescription;
    }

    /**
     * @param string $audioDescription
     */
    public function setAudioDescription($audioDescription)
    {
        $this->audioDescription = $audioDescription;
    }

    /**
     * @return ArrayCollection
     */
    public function getComments()
    {
        return $this->comments;
    }
}
