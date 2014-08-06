<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\DataFixtures\Required\Data;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\SurveyBundle\Entity\QuestionType;

class LoadQuestionTypeData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $multipleChoiceType = new QuestionType();
        $multipleChoiceType->setName('multiple_choice');
        $manager->persist($multipleChoiceType);

        $openEndedType = new QuestionType();
        $openEndedType->setName('open_ended');
        $manager->persist($openEndedType);

        $manager->flush();
    }
}