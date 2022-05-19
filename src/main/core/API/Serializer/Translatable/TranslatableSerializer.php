<?php

namespace Claroline\CoreBundle\API\Serializer\Translatable;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Content;
use Claroline\CoreBundle\Entity\ContentTranslation;
use Claroline\CoreBundle\Manager\LocaleManager;
use Symfony\Component\HttpFoundation\RequestStack;

class TranslatableSerializer
{
    use SerializerTrait;

    private $om;
    private $localeManager;
    private $requestStack;
    private $translations;

    public function __construct(
        ObjectManager $om,
        LocaleManager $localeManager,
        RequestStack $requestStack
    ) {
        $this->om = $om;
        $this->localeManager = $localeManager;
        $this->requestStack = $requestStack;

        $this->translations = $om->getRepository(ContentTranslation::class);
    }

    public function getName(): string
    {
        return 'translatable_content';
    }

    public function getClass(): string
    {
        return Content::class;
    }

    public function serialize(Content $content, array $options = [])
    {
        $translations = [];

        if ($content->getId()) {
            if (isset($options['property'])) {
                $found = $this->translations->findTranslations($content);

                foreach ($found as $lang => $text) {
                    if (isset($text[$options['property']])) {
                        $translations[$lang] = $text[$options['property']];
                    }
                }
            }
        } else {
            $locales = $this->localeManager->getEnabledLocales();

            foreach ($locales as $locale) {
                //there is an inconsistency here atm.
                if ('fr' === $locale) {
                    $locale = 'fr_FR';
                }

                $translations[$locale] = null;
            }
        }

        return $translations;
    }

    public function deserialize(array $data, Content $content, array $options = [])
    {
        // TODO : rewrite. persist/flush are not allowed in serializers
        foreach ($data as $locale => $text) {
            //not handled by the crud for now
            $method = 'set'.ucfirst($options['property']);
            $content->$method(null);
            $content->setTranslatableLocale($locale);
            $content->setModified();
            $this->om->persist($content);
        }

        if (isset($options['property'])) {
            foreach ($data as $locale => $text) {
                //not handled by the crud for now
                $method = 'set'.ucfirst($options['property']);
                $content->$method($text);
                $content->setTranslatableLocale($locale);
                $content->setModified();
                $this->om->persist($content);
            }
        }

        $this->om->flush();

        $this->sipe('type', 'setType', $data, $content);
        $content->setTranslatableLocale(
            $this->localeManager->getUserLocale($this->requestStack->getMasterRequest())
        );
    }
}
