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
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\SurveyBundle\Entity\QuestionType"
     * )
     * @ORM\JoinColumn(
     *     name="question_type_id",
     *     nullable=false,
     *     onDelete="CASCADE"
     * )
     */
    protected $questionType;

    /**
     * @param QuestionType $questionType
     */
    public function setQuestionType(QuestionType $questionType)
    {
        $this->questionType = $questionType;
    }

    /**
     * @return QuestionType
     */
    public function getQuestionType()
    {
        return $this->questionType;
    }
}
