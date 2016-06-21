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

use Claroline\CoreBundle\Entity\Content;
use Claroline\CoreBundle\Manager\ContentManager;
use Claroline\CoreBundle\Manager\LocaleManager;
use JMS\DiExtraBundle\Annotation\FormType;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @Service("claroline.form.content")
 * @FormType(alias = "content")
 */
class ContentType extends AbstractType
{
    private $langs;
    private $contentManager;
    private $tinymce;

    /**
     * @InjectParams({
     *     "localeManager" = @Inject("claroline.manager.locale_manager"),
     *     "contentManager" = @Inject("claroline.manager.content_manager")
     * })
     */
    public function __construct(LocaleManager $localeManager, ContentManager $contentManager)
    {
        $this->langs = $localeManager->getAvailableLocales();
        $this->contentManager = $contentManager;
        $this->tinymce = true;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $translatedContent = array();

        if ($builder->getData() instanceof Content) {
            $translatedContent = $this->contentManager->getTranslatedContent(
                array('id' => $builder->getData()->getId())
            );
        } elseif (is_array($builder->getData())) {
            $translatedContent = $builder->getData();
        }

        if (isset($options['theme_options']['tinymce']) && !$options['theme_options']['tinymce']) {
            $this->tinymce = false;
        }

        if (!empty($this->langs)) {
            foreach ($this->langs as $lang) {
                if (isset($translatedContent[$lang])) {
                    $builder->add(
                        $lang,
                        'base_content',
                        array(
                            'theme_options' => array('tinymce' => $this->tinymce),
                            'data' => $translatedContent[$lang],
                        )
                    );
                } else {
                    $builder->add($lang, 'base_content', array('theme_options' => array('tinymce' => $this->tinymce)));
                }
            }
        }
    }

    public function getName()
    {
        return 'content';
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

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        parent::finishView($view, $form, $options);

        $themeOptions = array(
            'contentTitle' => true,
            'contentText' => true,
            'titlePlaceHolder' => 'optional_title',
            'textPlaceHolder' => 'create_content',
        );

        foreach ($themeOptions as $option => $defaultValue) {
            if (isset($options['theme_options']) && isset($options['theme_options'][$option])) {
                $view->vars[$option] = $options['theme_options'][$option];
            } else {
                $view->vars[$option] = $defaultValue;
            }
        }
    }
}
