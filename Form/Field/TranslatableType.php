<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form\Field;

use Gedmo\Translatable\Translatable;
use Claroline\CoreBundle\Manager\TranslationManager;
use Claroline\CoreBundle\Manager\LocaleManager;
use JMS\DiExtraBundle\Annotation\FormType;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @Service("claroline.form.translatable")
 * @FormType(alias = "translatable")
 */
class TranslatableType extends AbstractType
{
    private $langs;
    private $contentManager;
    private $tinymce;

    /**
     * @InjectParams({
     *     "localeManager" = @Inject("claroline.manager.locale_manager"),
     *     "translationManager" = @Inject("claroline.manager.translation_manager")
     * })
     */
    public function __construct(LocaleManager $localeManager, TranslationManager $translationManager)
    {
        $this->langs = $localeManager->getAvailableLocales();
        $this->translationManager = $translationManager;
        $this->tinymce = true;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $translatedContent = array();

        if ($builder->getData() instanceof Translatable) {
            $translatedContent = $this->translationManager->getTranslatedContent($builder->getData());
        } elseif (is_array($builder->getData())) {
            $translatedContent = $builder->getData();
        }

        if (isset($options['theme_options']['tinymce']) and !$options['theme_options']['tinymce']) {
            $this->tinymce = false;
        }

        foreach ($this->langs as $lang) {
            if (isset($translatedContent[$lang])) {
                $builder->add(
                    $lang,
                    'base_translatable',
                    array(
                        'theme_options' => array('tinymce' => $this->tinymce),
                        'data' => $translatedContent[$lang]
                    )
                );
            } else {
                $builder->add(
                    $lang,
                    'base_translatable',
                    array('theme_options' => array('tinymce' => $this->tinymce)
                )
            );
            }
        }
    }

    public function getName()
    {
        return 'translatable';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'mapped' => false,
                'attr' => array('class' => 'content-element content-translatable relative'),
            )
        );
    }
}
