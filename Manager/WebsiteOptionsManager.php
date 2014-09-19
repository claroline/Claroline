<?php
/**
 * Created by PhpStorm.
 * User: panos
 * Date: 9/2/14
 * Time: 3:03 PM
 */

namespace Icap\WebsiteBundle\Manager;

use Icap\WebsiteBundle\Form\WebsiteOptionsType;
use JMS\DiExtraBundle\Annotation as DI;
use Doctrine\ORM\EntityManager;
use Icap\WebsiteBundle\Entity\Website;
use Icap\WebsiteBundle\Entity\WebsiteOptions;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class WebsiteOptionsManager
 * @package Icap\WebsiteBundle\Manager
 *
 * @DI\Service("icap_website.manager.options")
 */
class WebsiteOptionsManager {
    /**
     * @var \Symfony\Component\Form\FormFactory
     */
    protected $formFactory;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * Constructor
     *
     * @DI\InjectParams({
     *      "formFactory" = @DI\Inject("form.factory"),
     *      "entityManager" = @DI\Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct (FormFactory $formFactory, EntityManager $entityManager)
    {
        $this->formFactory = $formFactory;
        $this->entityManager = $entityManager;
    }

    public function processForm(WebsiteOptions $options, array $parameters, $method = "PUT")
    {
        $form = $this->formFactory->create(new WebsiteOptionsType(), $options, array('method' => $method));
        $form->submit($parameters, 'PATCH' !== $method);
        if ($form->isValid()) {
            $options = $form->getData();

            $this->entityManager->persist($options);
            $this->entityManager->flush();

            return $options->jsonSerialize();
        }

        throw new \InvalidArgumentException();
    }

    public function handleUploadImageFile(WebsiteOptions $options, UploadedFile $uploadedFile, $imageStr)
    {
        if ($uploadedFile->getMimeType()=="image/png" || $uploadedFile->getMimeType()=="image/jpg" || $uploadedFile->getMimeType()=="image/jpeg") {
            $newFileName = $uploadedFile->getClientOriginalName();
            $oldFileName = null;
            $getImageValue = 'get'.ucfirst($imageStr);
            $setImageValue = 'set'.ucfirst($imageStr);
            if ($options->$getImageValue() !== $newFileName) {
                $oldFileName = $options->$getImageValue();
                $options->$setImageValue($newFileName);
                try{
                    $uploadedFile->move($options->getUploadRootDir(), $newFileName);
                    $this->entityManager->persist($options);
                    $this->entityManager->flush();
                } catch(\Exception $e) {
                    if (file_exists($options->getUploadRootDir() . DIRECTORY_SEPARATOR .$newFileName)) {
                        unlink($options->getUploadRootDir() . DIRECTORY_SEPARATOR . $newFileName);
                    }
                    $options->$setImageValue($oldFileName);
                    throw new \InvalidArgumentException();
                }
                if (null !== $oldFileName && !filter_var($oldFileName, FILTER_VALIDATE_URL) && file_exists($options->getUploadRootDir() . DIRECTORY_SEPARATOR . $oldFileName)) {
                    unlink($options->getUploadRootDir() . DIRECTORY_SEPARATOR . $oldFileName);
                }
            }

            return array($imageStr => $options->getWebPath($imageStr));
        } else {
            throw new \InvalidArgumentException();
        }
    }
    
    public function handleUpdateImageURL(WebsiteOptions $options, $newPath, $imageStr)
    {
        $getImageValue = 'get'.ucfirst($imageStr);
        $setImageValue = 'set'.ucfirst($imageStr);
        $oldPath = $options->$getImageValue();
        $options->$setImageValue($newPath);
        try{
            $this->entityManager->persist($options);
            $this->entityManager->flush();
        } catch(\Exception $e) {
            $options->$setImageValue($oldPath);
            throw new \InvalidArgumentException();
        }
        if(null !== $oldPath && !filter_var($oldPath, FILTER_VALIDATE_URL) && file_exists($options->getUploadRootDir() . DIRECTORY_SEPARATOR . $oldPath)) {
            unlink($options->getUploadRootDir() . DIRECTORY_SEPARATOR . $oldPath);
        }

        return array($imageStr => $options->getWebPath($imageStr));
    }
} 