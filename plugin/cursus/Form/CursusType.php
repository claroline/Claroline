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
use Doctrine\ORM\EntityRepository;
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
        $details = is_null($this->cursus) ? [] : $this->cursus->getDetails();
        $color = isset($details['color']) ? $details['color'] : null;

        $builder->add(
            'title',
            'text',
            ['required' => true]
        );
        $builder->add(
            'code',
            'text',
            ['required' => false]
        );
        $builder->add(
            'description',
            'textarea',
            ['required' => false]
        );
        $builder->add(
            'workspace',
            'entity',
            [
                'class' => 'Claroline\CoreBundle\Entity\Workspace\Workspace',
                'choice_translation_domain' => true,
                'required' => false,
                'expanded' => false,
                'multiple' => false,
                'property' => 'nameAndCode',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('w')
                            ->where('w.isPersonal = false')
                            ->orderBy('w.name', 'ASC');
                },
                'label' => 'workspace',
                'translation_domain' => 'platform',
            ]
        );
        $builder->add(
            'blocking',
            'choice',
            [
                'choices' => ['yes' => true, 'no' => false],
                'label' => 'blocking',
                'required' => true,
                'choices_as_values' => true,
                'data' => is_null($this->cursus) ? false : $this->cursus->isBlocking(),
            ]
        );
        $builder->add(
            'color',
            'text',
            [
                'required' => false,
                'mapped' => false,
                'data' => $color,
                'label' => 'color',
                'translation_domain' => 'platform',
                'attr' => ['colorpicker' => 'hex'],
            ]
        );
    }

    public function getName()
    {
        return 'cursus_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'cursus']);
    }
}
