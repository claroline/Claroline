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

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

/**
 * FieldValueText.
 *
 * @ORM\Entity
 */
class FieldValueText extends FieldValue
{
    /**
     * @var string
     *
     * @Groups({
     *     "api_flashcard",
     *     "api_flashcard_note",
     *     "api_flashcard_card",
     *     "api_flashcard_deck"
     * })
     */
    protected $type = 'text';
}
