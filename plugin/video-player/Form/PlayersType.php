<?php
/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\VideoPlayerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PlayersType extends AbstractType
{
    public function __construct($player = null)
    {
        $this->player = $player;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'player',
            'choice',
            array(
                'choices' => array(
                    'videojs' => 'videojs',
                ),
                'multiple' => false,
                'expanded' => true,
                'label' => 'player',
                'data' => $this->player,
            )
        );
    }

    public function getName()
    {
        return 'player_type_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'platform'));
    }
}
