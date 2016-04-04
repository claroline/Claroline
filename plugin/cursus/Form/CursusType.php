<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Form;

use Claroline\CursusBundle\Entity\Cursus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CursusType extends AbstractType
{
    private $cursus;

    public function __construct(Cursus $cursus = null)
    {
        $this->cursus = $cursus;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $details = is_null($this->cursus) ? array() : $this->cursus->getDetails();
        $color = isset($details['color']) ? $details['color'] : null;

        $builder->add(
            'title',
            'text',
            array('required' => true)
        );
        $builder->add(
            'code',
            'text',
            array('required' => false)
        );
        $builder->add(
            'description',
            'tinymce',
            array('required' => false)
        );
        $builder->add(
            'workspace',
            'entity',
            array(
                'class' => 'Claroline\CoreBundle\Entity\Workspace\Workspace',
                'choice_translation_domain' => true,
                'required' => false,
                'expanded' => false,
                'multiple' => false,
                'property' => 'nameAndCode',
                'query_builder' => function (\Doctrine\ORM\EntityRepository $er) {
                    return $er->createQueryBuilder('w')
                            ->where('w.isPersonal = false')
                            ->orderBy('w.name', 'ASC');
                },
                'label' => 'workspace',
                'translation_domain' => 'platform'
            )
        );
        $builder->add(
            'blocking',
            'checkbox',
            array('required' => true)
        );
        $builder->add(
            'color',
            'text',
            array(
                'required' => false,
                'mapped' => false,
                'data' => $color,
                'label' => 'color',
                'translation_domain' => 'platform'
            )
        );
    }

    public function getName()
    {
        return 'cursus_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'cursus'));
    }
}
