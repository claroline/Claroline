<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener;

use Gedmo\Translatable\TranslatableListener;
use Doctrine\Common\EventArgs;

class LocaleListener extends TranslatableListener
{
    public function postLoad(EventArgs $args)
    {
        $this->setLocale();
        $ea = $this->getEventAdapter($args);
        $om = $ea->getObjectManager();
        $object = $ea->getObject();
        $meta = $om->getClassMetadata(get_class($object));
        $config = $this->getConfiguration($om, $meta->name);

        if (isset($config['fields'])) {
            $locale = $this->getTranslatableLocale($object, $meta);
            $oid = spl_object_hash($object);
        }

        if (isset($config['fields']) && $locale !== $this->getDefaultLocale()) {
            // fetch translations
            $translationClass = $this->getTranslationClass($ea, $config['useObjectClass']);
            $result = $ea->loadTranslations(
                $object,
                $translationClass,
                $locale,
                $config['useObjectClass']
            );
            // translate object's translatable properties
            foreach ($config['fields'] as $field) {
                $translated = '';
                foreach ($result as $entry) {
                    if ($entry['field'] === $field) {
                        $translated = $entry['content'];
                        break;
                    }
                }
                // update translation
                if ($this->isTranslatable($translated, $config, $field)) {
                    if ($translated !== '') {
                        $ea->setTranslationValue($object, $field, $translated);
                        // ensure clean changeset
                        $ea->setOriginalObjectProperty(
                            $om->getUnitOfWork(),
                            $oid,
                            $field,
                            $meta->getReflectionProperty($field)->getValue($object)
                        );
                    }
                }
            }
        }
    }

    private function isTranslatable($translated, $config, $field)
    {
        return
            $translated ||
            (!$this->getTranslationFallback() && (!isset($config['fallback'][$field]) ||
            !$config['fallback'][$field])) || ($this->getTranslationFallback() &&
            isset($config['fallback'][$field]) && !$config['fallback'][$field])
        ;
    }

    /**
     * Override the locale for the term of services.
     *
     * @todo it's not very pretty so we should find an other way to do it.
     */
    private function setLocale()
    {
        if (isset($_SESSION['_sf2_attributes'])) {
            if (isset($_SESSION['_sf2_attributes']['_locale'])) {
                $this->setTranslatableLocale($_SESSION['_sf2_attributes']['_locale']);
            }
        }
    }
}
