<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form\Profile;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Claroline\CoreBundle\Entity\Facet\Facet;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ProfileFacetFieldsType extends AbstractType
{
    public function __construct(
        Facet $facet,
        TranslatorInterface $translator
    ) {
        $this->facet = $facet;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($this->facet->getPanelFacets() as $panel) {
            $type = new ProfilePanelFieldsType($panel, $this->translator);
            $type->buildForm($builder, $options);
        }
    }

    public function getName()
    {
        return 'profile_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
        ->setDefaults(
            array(
                'translation_domain' => 'platform',
                'validation_groups' => array('registration', 'Default'),
            )
        );
    }
}
