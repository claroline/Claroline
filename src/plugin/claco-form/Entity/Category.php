<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ClacoFormBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Claroline\ClacoFormBundle\Repository\CategoryRepository;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_clacoformbundle_category')]
#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    use Id;
    use Uuid;

    #[ORM\Column(name: 'category_name')]
    private ?string $name = null;

    #[ORM\JoinColumn(name: 'claco_form_id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: ClacoForm::class, inversedBy: 'categories')]
    private ?ClacoForm $clacoForm = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\JoinTable(name: 'claro_clacoformbundle_category_manager')]
    #[ORM\ManyToMany(targetEntity: User::class)]
    private Collection $managers;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $details = [];

    public function __construct()
    {
        $this->refreshUuid();
        $this->managers = new ArrayCollection();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getClacoForm(): ?ClacoForm
    {
        return $this->clacoForm;
    }

    /**
     * @internal use ClacoForm::addCategory/ClacoForm::removeCategory
     */
    public function setClacoForm(ClacoForm $clacoForm = null): void
    {
        $this->clacoForm = $clacoForm;
    }

    /**
     * @return User[]
     */
    public function getManagers(): array
    {
        return $this->managers->toArray();
    }

    public function addManager(User $manager): void
    {
        if (!$this->managers->contains($manager)) {
            $this->managers->add($manager);
        }
    }

    public function removeManager(User $manager): void
    {
        if ($this->managers->contains($manager)) {
            $this->managers->removeElement($manager);
        }
    }

    public function emptyManagers(): void
    {
        $this->managers->clear();
    }

    public function getDetails(): ?array
    {
        return $this->details;
    }

    public function setDetails(array $details): void
    {
        $this->details = $details;
    }

    public function getColor(): ?string
    {
        return !is_null($this->details) && isset($this->details['color']) ? $this->details['color'] : null;
    }

    public function setColor(string $color): void
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['color'] = $color;
    }

    public function getNotifyAddition(): bool
    {
        return !is_null($this->details) && isset($this->details['notify_addition']) ? $this->details['notify_addition'] : true;
    }

    public function setNotifyAddition(bool $notifyAddition): void
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['notify_addition'] = $notifyAddition;
    }

    public function getNotifyEdition(): bool
    {
        return !is_null($this->details) && isset($this->details['notify_edition']) ? $this->details['notify_edition'] : true;
    }

    public function setNotifyEdition(bool $notifyEdition): void
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['notify_edition'] = $notifyEdition;
    }

    public function getNotifyRemoval(): bool
    {
        return !is_null($this->details) && isset($this->details['notify_removal']) ? $this->details['notify_removal'] : true;
    }

    public function setNotifyRemoval(bool $notifyRemoval): void
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['notify_removal'] = $notifyRemoval;
    }

    public function getNotifyPendingComment(): bool
    {
        return !is_null($this->details) && isset($this->details['notify_pending_comment']) ?
            $this->details['notify_pending_comment'] :
            true;
    }

    public function setNotifyPendingComment(bool $notifyPendingComment): void
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['notify_pending_comment'] = $notifyPendingComment;
    }
}
