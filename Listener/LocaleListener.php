<?php

namespace Claroline\CoreBundle\Listener;

use Gedmo\Translatable\TranslatableListener;
use Doctrine\Common\EventArgs;

class LocaleListener extends TranslatableListener
{
    public function postLoad(EventArgs $args)
    {
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
                foreach ((array)$result as $entry) {
                    if ($entry['field'] == $field) {
                        $translated = $entry['content'];
                        break;
                    }
                }
                // update translation
                if ($translated
                    || (!$this->getTranslationFallback() && (!isset($config['fallback'][$field]) || !$config['fallback'][$field]))
                    || ($this->getTranslationFallback() && isset($config['fallback'][$field]) && !$config['fallback'][$field])
                ) {
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
}
