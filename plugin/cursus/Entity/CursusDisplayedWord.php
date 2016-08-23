<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="claro_cursusbundle_cursus_displayed_word")
 * @ORM\Entity
 */
class CursusDisplayedWord
{
    public static $defaultKey = ['cursus', 'course', 'session'];

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(unique=true)
     */
    protected $word;

    /**
     * @ORM\Column(name="displayed_name", nullable=true)
     */
    protected $displayedWord;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getWord()
    {
        return $this->word;
    }

    public function setWord($word)
    {
        $this->word = $word;
    }

    public function getDisplayedWord()
    {
        return $this->displayedWord;
    }

    public function setDisplayedWord($displayedword)
    {
        $this->displayedWord = $displayedword;
    }
}
