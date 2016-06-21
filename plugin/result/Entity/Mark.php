<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ResultBundle\Entity;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="claro_result_mark")
 * @ORM\Entity(repositoryClass="Claroline\ResultBundle\Repository\MarkRepository"))
 */
class Mark implements \JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column
     */
    private $value;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="Result", inversedBy="marks")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $result;

    /**
     * @param Result $result
     * @param User   $user
     * @param string $value
     */
    public function __construct(Result $result, User $user, $value)
    {
        $this->result = $result;
        $this->user = $user;
        $this->value = $value;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return Result
     */
    public function getResult()
    {
        return $this->result;
    }

    public function jsonSerialize()
    {
        // NOTE: this serialization format matches the data format used in MarkRepository

        return [
            'id' => $this->user->getId(),
            'name' => "{$this->user->getFirstName()} {$this->user->getLastName()}",
            'mark' => $this->value,
            'markId' => $this->id,
        ];
    }
}
