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
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $content = array();

        if ($builder->getData() instanceof Content) {
            $translatedContent = $this->contentManager->getTranslatedContent(
                array('id' => $builder->getData()->getId())
            );
        }

        if (!empty($this->langs)) {

            foreach ($this->langs as $lang) {

                $title = '';
                $content = '';

                if (isset($translatedContent[$lang]) and isset($translatedContent[$lang]['title'])) {
                    $title = $translatedContent[$lang]['title'];
                }

                if (isset($translatedContent[$lang]) and isset($translatedContent[$lang]['content'])) {
                    $content = $translatedContent[$lang]['content'];
                }

                $builder->add('title_'.$lang, 'text', array('mapped' => false, 'data' => $title));
                $builder->add('content_'.$lang, 'tinymce', array('mapped' => false, 'data' => $content));
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

        if (!empty($this->langs)) {
            $view->vars['langs'] = $this->langs;
        }
    }
}
