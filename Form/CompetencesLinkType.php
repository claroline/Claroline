<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\Common\Collections\ArrayCollection;

class CompetencesLinkType extends AbstractType {

    private $competences;
    private $isAdmin;
    private $langs;

    public function __construct(array $competences, $isAdmin)
    {
        $this->competences = new ArrayCollection($competences);
        $this->isAdmin = $isAdmin;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
        'competences',
        'entity',
        array(
            'mapped' => false,
            'data' => $this->competences,
            'class' => 'Claroline\CoreBundle\Entity\Competence\Competence',
            'expanded' => true,
            'multiple' => true,
            'property' => 'name',
            'label' => 'competences'
        )
    );
    }

    public function getName()
    {
        return 'competences_link_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'platform'));
    }
}