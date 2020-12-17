<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\ORM\Mapping as ORM;

/**
 * TODO : finish implementation.
 */
class SavedSearch
{
    use Id;
    use Uuid;

    /**
     * @ORM\Column()
     *
     * @var string
     */
    private $name;

    /**
     * @ORM\Column()
     *
     * @var string
     */
    private $list;

    /**
     * @ORM\Column(type="json_array")
     *
     * @var array
     */
    private $filters = [];

    /**
     * The user who created the search.
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var User
     */
    private $user;

    /**
     * Get name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set name.
     *
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * Get the list.
     *
     * @return string
     */
    public function getList(): string
    {
        return $this->list;
    }

    /**
     * Set the list.
     *
     * @param string $list
     */
    public function setList(string $list)
    {
        $this->list = $list;
    }

    /**
     * Get saved filters.
     *
     * @return array
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * Set saved filters.
     *
     * @param array $filters
     */
    public function setFilters(array $filters)
    {
        $this->filters = $filters;
    }

    /**
     * Get the search user.
     *
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * Set the search user.
     *
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }
}
