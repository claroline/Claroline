<?php

namespace Innova\MediaResourceBundle\Manager;

use Doctrine\ORM\EntityManager;
use Innova\MediaResourceBundle\Entity\Options;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("innova_media_resource.manager.media_resource_options")
 */
class OptionsManager
{
    protected $em;

    /**
     * @DI\InjectParams({
     *      "em"          = @DI\Inject("doctrine.orm.entity_manager")
     * })
     *
     * @param ContainerInterface $container
     * @param EntityManager      $em
     **/
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getRepository()
    {
        return $this->em->getRepository('InnovaMediaResourceBundle:Options');
    }

    public function update(Options $options, $data)
    {
        $valid = true;
        if ($this->checkData($data)) {
            $newOptions = $data['options'];
            $options->setTtsLanguage($newOptions['lang']);
            $options->setShowTextTranscription($newOptions['showTextTranscription']);
            $options->setMode($newOptions['mode']);
            $this->em->persist($options);
            $this->em->flush();
        } else {
            $valid = false;
        }

        return $valid;
    }

    private function checkData($data)
    {
        if (!isset($data['options'])) {
            return false;
        }
        $toCheck = $data['options'];

        return isset($toCheck['id']) && isset($toCheck['mode']) && isset($toCheck['lang']) && isset($toCheck['showTextTranscription']);
    }
}
