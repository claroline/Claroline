<?php

namespace Claroline\CoreBundle\API\Serializer\Translatable;

use Claroline\CoreBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\Entity\Content;
use Claroline\CoreBundle\Manager\LocaleManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @DI\Service("claroline.serializer.translatable")
 * @DI\Tag("claroline.serializer")
 */
class TranslatableSerializer
{
    use SerializerTrait;

    /**
     * GroupSerializer constructor.
     *
     * @DI\InjectParams({
     *     "om"            = @DI\Inject("claroline.persistence.object_manager"),
     *     "localeManager" = @DI\Inject("claroline.manager.locale_manager"),
     *     "container"     = @DI\Inject("service_container")
     * })
     *
     * @param ObjectManager      $om
     * @param LocaleManager      $localeManager
     * @param ContainerInterface $container
     */
    public function __construct(
        ObjectManager $om,
        LocaleManager $localeManager,
        ContainerInterface $container
    ) {
        $this->om = $om;
        $this->localeManager = $localeManager;
        $this->container = $container;
        $this->translations = $om->getRepository('ClarolineCoreBundle:ContentTranslation');
    }

    public function serialize(Content $content, array $options = [])
    {
        $translations = [];

        if ($content->getId()) {
            if (isset($options['property'])) {
                $found = $this->translations->findTranslations($content);

                foreach ($found as $lang => $text) {
                    $translations[$lang] = $text[$options['property']];
                }
            }
        } else {
            $locales = $this->localeManager->getAvailableLocales();

            foreach ($locales as $locale) {
                //there is an inconcistency here atm.
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
        foreach ($data as $locale => $text) {
            //not handled by the crud for now
            $method = 'set'.ucfirst($options['property']);
            $content->$method(null);
            $content->setTranslatableLocale($locale);
            $content->setModified();
            $this->om->persist($content);
            $this->om->flush();
        }

        if (isset($options['property'])) {
            foreach ($data as $locale => $text) {
                //not handled by the crud for now
                $method = 'set'.ucfirst($options['property']);
                $content->$method($text);
                $content->setTranslatableLocale($locale);
                $content->setModified();
                $this->om->persist($content);
                $this->om->flush();
            }
        }

        $this->sipe('type', 'setType', $data, $content);
        $content->setTranslatableLocale($this->localeManager->getUserLocale($this->container->get('request')));
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return 'Claroline\CoreBundle\Entity\Content';
    }
}
