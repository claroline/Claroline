<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Update;

use Claroline\InstallationBundle\Repository\UpdaterExecutionRepository;
use Doctrine\DBAL\Types\Types;
use Claroline\AppBundle\Entity\Identifier\Id;
use Doctrine\ORM\Mapping as ORM;

/**
 * Represents an updater than has been executed.
 * It can be used to avoid executing the same updater more than once.
 */
#[ORM\Table(name: 'claro_update')]
#[ORM\Entity(repositoryClass: UpdaterExecutionRepository::class)]
class UpdaterExecution
{
    use Id;

    /**
     * @var string the FQCN of the executed updater
     */
    #[ORM\Column(name: 'updater_class', type: Types::STRING, unique: true)]
    private $updaterClass;

    public function __construct(string $updaterClass)
    {
        $this->updaterClass = $updaterClass;
    }

    public function getUpdaterClass(): string
    {
        return $this->updaterClass;
    }
}
