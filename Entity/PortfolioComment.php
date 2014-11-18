<?php

namespace Icap\PortfolioBundle\Entity;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="icap__portfolio_comments")
 * @ORM\Entity
 */
class PortfolioComment
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Icap\PortfolioBundle\Entity\Portfolio", inversedBy="comments")
     * @ORM\JoinColumn(name="portfolio_id", referencedColumnName="id", nullable=false)
     */
    protected $portfolio;

    /**
     * @var \Claroline\CoreBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="sender_id", referencedColumnName="id", nullable=false)
     */
    protected $sender;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    protected $message;

    /**
     * @var \Datetime $sendingDate
     *
     * @ORM\Column(type="datetime", name="sending_date")
     * @Gedmo\Timestampable(on="create")
     */
    protected $sendingDate;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return PortfolioComment
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return \Icap\PortfolioBundle\Entity\Portfolio
     */
    public function getPortfolio()
    {
        return $this->portfolio;
    }

    /**
     * @param mixed $portfolio
     *
     * @return PortfolioComment
     */
    public function setPortfolio($portfolio)
    {
        $this->portfolio = $portfolio;

        return $this;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     *
     * @return PortfolioComment
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return User
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @param User $sender
     *
     * @return PortfolioComment
     */
    public function setSender(User $sender)
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * @return \Datetime
     */
    public function getSendingDate()
    {
        return $this->sendingDate;
    }

    /**
     * @param \Datetime $sendingDate
     *
     * @return PortfolioComment
     */
    public function setSendingDate(\Datetime $sendingDate)
    {
        $this->sendingDate = $sendingDate;

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        $sender    = $this->getSender();

        return array(
            'id'      => $this->getId(),
            'sender'  => array(
                'lastName'  => $sender->getLastName(),
                'firstName' => $sender->getFirstName()
            ),
            'message' => $this->getMessage(),
            'date'    => $this->getSendingDate()->format(DATE_W3C)
        );
    }
}