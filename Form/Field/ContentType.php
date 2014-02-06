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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\FormType;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use Claroline\CoreBundle\Manager\LocaleManager;
use Claroline\CoreBundle\Manager\ContentManager;
use Claroline\CoreBundle\Entity\Content;

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
     *     "localeManager" = @Inject("claroline.common.locale_manager"),
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

        if (isset($options['theme_options']['tinymce']) and !$options['theme_options']['tinymce']) {
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
                            'data' => $translatedContent[$lang]
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

        $view->vars['contentTitle'] = true;

        if (isset($options['theme_options']) and isset($options['theme_options']['contentTitle'])) {
            $view->vars['contentTitle'] = $options['theme_options']['contentTitle'];
        }
    }
}
