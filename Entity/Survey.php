<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\SurveyBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_survey")
 */
class Survey extends AbstractResource
{
    /**
     * @ORM\Column(name="question_type")
     */
    private $questionType;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isPublished = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isClosed = false;

    /**
     * @param string
     */
    public function setQuestionType($questionType)
    {
        $this->questionType = $questionType;
    }

    /**
     * @return string
     */
    public function getQuestionType()
    {
        return $this->questionType;
    }

    /**
     * @param boolean $isClosed
     */
    public function setIsClosed($isClosed)
    {
        $this->isClosed = $isClosed;
    }

    /**
     * @return boolean
     */
    public function isClosed()
    {
        return $this->isClosed;
    }

    /**
     * @param boolean $isPublished
     */
    public function setIsPublished($isPublished)
    {
        $this->isPublished = $isPublished;
    }

    /**
     * @return boolean
     */
    public function isPublished()
    {
        return $this->isPublished;
    }
}
