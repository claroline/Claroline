<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\FlashCardBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="claro_flashcard")
 * @ORM\Entity(repositoryClass="Claroline\FlashCardBundle\Repository\FlashCardRepository"))
 */
class FlashCard extends AbstractResource
{

    // No attribute implemented yet.

    public function __construct(\DateTime $date = null)
    {
        // Not imlemented yet.
    }
}
