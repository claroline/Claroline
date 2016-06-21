<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\TagBundle\Form;

use Claroline\TagBundle\Entity\ResourcesTagsWidgetConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class ResourcesTagsWidgetConfigurationType extends AbstractType
{
    private $config;

    public function __construct(ResourcesTagsWidgetConfig $config)
    {
        $this->config = $config;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $details = $this->config->getDetails();
        $nbTags = isset($details['nb_tags']) ? $details['nb_tags'] : 10;

        $builder->add(
            'nbTags',
            'integer',
            array(
                'required' => true,
                'mapped' => false,
                'label' => 'number_of_tags',
                'data' => $nbTags,
                'constraints' => array(
                    new NotBlank(),
                    new Range(array('min' => 0)),
                ),
                'attr' => array('min' => 0),
            )
        );
    }

    public function getName()
    {
        return 'resources_tags_widget_configuration_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'tag'));
    }
}
