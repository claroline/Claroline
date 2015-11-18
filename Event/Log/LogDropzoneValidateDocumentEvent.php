<?php

namespace Innova\CollecticielBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\CoreBundle\Event\Log\NotifiableInterface;
use Innova\CollecticielBundle\Entity\Document;
use Innova\CollecticielBundle\Entity\Dropzone;

class LogDropzoneValidateDocumentEvent extends AbstractLogResourceEvent implements NotifiableInterface {

    const ACTION = 'resource-innova_collecticiel-dropzone_validate_document';

    protected $document;
    protected $dropzone;
    protected $details;
    private $userIds = array();

    /**
     * @param Wiki $wiki
     * @param Section $section
     * @param Contribution $contribution
    */
    public function __construct(Document $document, Dropzone $dropzone, $userIds)
    {


//        $this->resourceNodeId = $dropzone->getDrops()[0]->getUser()->getId();

//        $dropId = $document->getDrop()->getId(); //->getDropzone()->getId();

//var_dump($dp);
//var_dump($document);die();
        $this->document = $document;
        $this->type = $dropzone->getResourceNode()->getName();
        $this->userIds = $userIds;
echo "<pre>";
var_dump($this->userIds);
echo "</pre>";
//die();

        $this->details = array(
//            'newState'=> $this->newState
        );

        // Récupération du nom et du prénom
        $this->firstName = $document->getSender()->getFirstName();
        $this->lastName = $document->getSender()->getLastName();

//var_dump($this->firstName);
//var_dump($this->lastName);
//var_dump($this->type);
//die();

        parent::__construct($dropzone->getResourceNode(), $this->details);
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return array(self::DISPLAYED_WORKSPACE);
    }

    public function getDocument()
    {
        return $this->$document;
    }

    /**
     * Get sendToFollowers boolean.
     * 
     * @return boolean
     */
    public function getSendToFollowers()
    {
        return true;
    }

    /**
     * Get includeUsers array of user ids.
     * Reports are only reported to user witch have the manager role
     * @return array
     */
    public function getIncludeUserIds()
    {
       return $this->userIds; 
    }

    /**
     * Get excludeUsers array of user ids.
     *
     * @return array
     */
    public function getExcludeUserIds()
    {
        return array();
    }

    /**
     * Get actionKey string.
     *
     * @return string
     */
    public function getActionKey()
    {
        return $this::ACTION;
    }

    /**
     * Get iconTypeUrl string.
     *
     * @return string
     */
    public function getIconKey()
    {
        return "dropzone";
    }

    /**
     * Get details
     *
     * @return array
     */
    public function getNotificationDetails()
    {

        $notificationDetails = array_merge($this->details, array());

        $notificationDetails['resource'] = array(
            'id' => $this->document->getId(),
            'name' => $this->firstName . " " . $this->lastName, // $this->resource->getName(),
            'type' => $this->type
        );

var_dump($notificationDetails);        
//die();

        return $notificationDetails;
    }

    /**
     * Get if event is allowed to create notification or not
     *
     * @return boolean
     */
    public function isAllowedToNotify()
    {
        return true;
    }
}
