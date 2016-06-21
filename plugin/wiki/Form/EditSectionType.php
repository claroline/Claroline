<?php

namespace Icap\WikiBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Icap\WikiBundle\Manager\SectionManager;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("icap.wiki.section_edit_type")
 */
class EditSectionType extends AbstractType
{
    /** @var \Icap\WikiBundle\Manager\SectionManager */
    protected $sectionManager;

    /**
     * @DI\InjectParams({
     *     "sectionManager" = @DI\Inject("icap.wiki.section_manager")
     * })
     */
    public function __construct(SectionManager $sectionManager)
    {
        $this->sectionManager = $sectionManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('activeContribution', new ContributionType(), array(
            'label' => false,
            )
        );

        $sectionManager = $this->sectionManager;
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($sectionManager) {
                $form = $event->getForm();
                $data = $event->getData();

                $isRoot = $data->isRoot();
                if ($isRoot === false && $data->getIsWikiAdmin()) {
                    $form
                        ->add('visible', 'checkbox', array(
                                'required' => false,
                                'theme_options' => array('label_width' => 'col-md-12', 'control_width' => 'hidden'),
                            )
                        )
                        ->add('position', 'choice', array(
                                'choices' => $sectionManager->getArchivedSectionsForPosition($data),
                                'theme_options' => array('label_width' => 'col-md-12', 'control_width' => 'col-md-12'),
                            )
                        )
                        ->add('brother', 'checkbox', array(
                                'required' => false,
                                'theme_options' => array('label_width' => 'col-md-12', 'control_width' => 'hidden'),
                            )
                        );
                }
            }
        );
    }

    public function getName()
    {
        return 'icap_wiki_edit_section_type';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'icap_wiki',
            'data_class' => 'Icap\WikiBundle\Entity\Section',
            'sections' => array(),
            'isRootSection' => false,
        ));
    }
}
