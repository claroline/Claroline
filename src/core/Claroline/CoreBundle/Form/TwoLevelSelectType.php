<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Claroline\CoreBundle\Validator\Constraints\AdminWorkspaceTagUniqueName;
use Claroline\CoreBundle\Form\DataTransformer\DateRangeToTextTransformer;
use JMS\DiExtraBundle\Annotation as DI;


/**
 * @DI\Service("claroline.form.twolevelselect")
 * @DI\FormType(alias = "twolevelselect")
 */
class TwoLevelSelectType extends AbstractType
{
    public function getParent()
    {
        return 'choice';
    }

    public function getName()
    {
        return 'twolevelselect';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
        ->setDefaults(
            array(
                'translation_domain' => 'platform'
            )
        );
    }
}