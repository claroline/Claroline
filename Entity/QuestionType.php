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

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\MappedSuperclass
 */
/**
 * @ORM\Entity(repositoryClass="Claroline\SurveyBundle\Repository\QuestionTypeRepository")
 * @ORM\Table(
 *     name="claro_survey_question_type",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="unique_question_type_name",
 *             columns={"type_name"}
 *         )
 *     }
 * )
 * @DoctrineAssert\UniqueEntity("name")
 */
class QuestionType
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="type_name")
     */
    protected $name;
    
    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }
}
