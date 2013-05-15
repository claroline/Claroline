<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Claroline\CoreBundle\Validator\Constraints\AdminWorkspaceTagUniqueName;
use JMS\DiExtraBundle\Annotation as DI;


/**
 * @DI\Service("claroline.form.simpleautocomplete")
 * @DI\FormType(alias = "simpleautocomplete")
 */
class SimpleAutoCompleteType extends AbstractType
{
    public function getParent()
    {
        return 'text';
    }

    public function getName()
    {
        return 'simpleautocomplete';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
        ->setDefaults(
            array(
                'translation_domain' => 'platform',
                'entity_reference' => null
            )
        );
    }
}