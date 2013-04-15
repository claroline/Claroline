<?php
namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ToolType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('displayName', 'text');
    }

    public function getName()
    {
        return 'tool_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
        ->setDefaults(
            array(
                'classe' => 'Claroline\CoreBundle\Entity\Tool\Tool.php',
                'translation_domain' => 'platform'
                )
        );
    }
}