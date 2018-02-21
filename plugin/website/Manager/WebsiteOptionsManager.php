<?php
/**
 * Created by PhpStorm.
 * User: panos
 * Date: 9/2/14
 * Time: 3:03 PM.
 */

namespace Icap\WebsiteBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Icap\WebsiteBundle\Entity\WebsiteOptions;
use Icap\WebsiteBundle\Form\WebsiteOptionsType;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class WebsiteOptionsManager.
 *
 * @DI\Service("icap.website.options.manager")
 */
class WebsiteOptionsManager
{
    /**
     * @var \Symfony\Component\Form\FormFactory
     */
    protected $formFactory;

    /**
     * @var ObjectManager
     */
    protected $om;

    /**
     * @var \JMS\Serializer\Serializer
     */
    protected $serializer;

    protected $webDir;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *      "formFactory"   = @DI\Inject("form.factory"),
     *      "objectManager" = @DI\Inject("claroline.persistence.object_manager"),
     *      "serializer"    = @DI\Inject("jms_serializer"),
     *      "webDir"        = @DI\Inject("%claroline.param.web_directory%")
     * })
     *
     * @param FormFactory   $formFactory
     * @param ObjectManager $objectManager
     * @param Serializer    $serializer
     * @param $webDir
     */
    public function __construct(FormFactory $formFactory, ObjectManager $objectManager, Serializer $serializer, $webDir)
    {
        $this->formFactory = $formFactory;
        $this->om = $objectManager;
        $this->serializer = $serializer;
        $this->webDir = $webDir;
    }

    public function processForm(WebsiteOptions $options, array $parameters, $method = 'PUT')
    {
        $form = $this->formFactory->create(new WebsiteOptionsType(), $options, ['method' => $method]);
        $form->submit($parameters, 'PATCH' !== $method);
        if ($form->isValid()) {
            $options = $form->getData();
            $this->om->persist($options);
            $this->om->flush();
            $serializationContext = new SerializationContext();
            $serializationContext->setSerializeNull(true);

            return json_decode($this->serializer->serialize(
                    $options,
                    'json',
                    $serializationContext
                ));
        } /*else {
            return $this->getErrorMessages($form);
        }*/

        throw new \InvalidArgumentException();
    }

    public function handleUploadImageFile(WebsiteOptions $options, UploadedFile $uploadedFile, $imageStr)
    {
        if ('image/png' === $uploadedFile->getMimeType() || 'image/jpg' === $uploadedFile->getMimeType() || 'image/jpeg' === $uploadedFile->getMimeType()) {
            $newFileName = sha1(uniqid(mt_rand(), true)).'.'.$uploadedFile->guessExtension();
            $oldFileName = null;
            $getImageValue = 'get'.ucfirst($imageStr);
            $setImageValue = 'set'.ucfirst($imageStr);
            $uploadDir = $this->webDir.DIRECTORY_SEPARATOR.$options->getUploadDir();
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $realpathUploadRootDir = realpath($uploadDir);
            if (false === $realpathUploadRootDir) {
                throw new \Exception(
                    sprintf(
                        "Invalid upload root dir '%s'for uploading website images.",
                        $uploadDir
                    )
                );
            }
            if ($options->$getImageValue() !== $newFileName) {
                $oldFileName = $options->$getImageValue();
                $options->$setImageValue($newFileName);
                try {
                    $uploadedFile->move($realpathUploadRootDir, $newFileName);
                    $this->om->persist($options);
                    $this->om->flush();
                } catch (\Exception $e) {
                    if (file_exists($realpathUploadRootDir.DIRECTORY_SEPARATOR.$newFileName)) {
                        unlink($realpathUploadRootDir.DIRECTORY_SEPARATOR.$newFileName);
                    }
                    $options->$setImageValue($oldFileName);
                    throw new \InvalidArgumentException($e->getMessage());
                }

                if (null !== $oldFileName && !filter_var($oldFileName, FILTER_VALIDATE_URL) && file_exists($realpathUploadRootDir.DIRECTORY_SEPARATOR.$oldFileName)) {
                    unlink($realpathUploadRootDir.DIRECTORY_SEPARATOR.$oldFileName);
                }
            }

            return [$imageStr => $options->getWebPath($imageStr)];
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
        try {
            $this->om->persist($options);
            $this->om->flush();
        } catch (\Exception $e) {
            $options->$setImageValue($oldPath);
            throw new \InvalidArgumentException();
        }
        if (
            null !== $oldPath
            && !filter_var($oldPath, FILTER_VALIDATE_URL)
            && file_exists($this->webDir.DIRECTORY_SEPARATOR.$options->getUploadDir().DIRECTORY_SEPARATOR.$oldPath)
        ) {
            unlink($this->webDir.DIRECTORY_SEPARATOR.$options->getUploadDir().DIRECTORY_SEPARATOR.$oldPath);
        }

        return [$imageStr => $options->getWebPath($imageStr)];
    }

    private function getErrorMessages(Form $form)
    {
        $errors = [];
        foreach ($form->getErrors() as $key => $error) {
            $template = $error->getMessageTemplate();
            $parameters = $error->getMessageParameters();

            foreach ($parameters as $var => $value) {
                $template = str_replace($var, $value, $template);
            }

            $errors[$key] = $template;
        }
        if ($form->count()) {
            foreach ($form as $child) {
                if (!$child->isValid()) {
                    $errors[$child->getName()] = $this->getErrorMessages($child);
                }
            }
        }

        return $errors;
    }
}
