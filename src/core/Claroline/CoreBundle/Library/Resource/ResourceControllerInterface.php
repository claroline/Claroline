<?php

namespace Claroline\CoreBundle\Library\Resource;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;

/**
 * is implemented by resource managers. It describes methods wich will be used
 * for each Resources by the Claroline\CoreBundle\Controller\ResourceController
 */
interface ResourceControllerInterface
{
    /**
     * Each manager works for a different entity Resource\ResourceTye.
     * Returns the ResourceType->getType() value.
     */
    public function getResourceType();

    /**
     * Returns the FormFactory->create(entity) value.
     */
    public function getForm();

    /**
     * Returns the form page.
     *
     * @param string $twigFile the twig file rendered
     * @param integer $id the resource parent id
     * @param string $type the ResourceType->getType() value the form will work with
     */
    public function getFormPage($twigFile, $id, $type);

    //todo: remove $id from add.
    /**
     * Return the Resource entity added.
     *
     * @param Form $form the form containing usefull datas
     * @param integer $id the ResourceInstance parent. It may not be usefull.
     * @param User $user the user adding the new resource. It may not be usefull.
     */
    public function add($form, $id, User $user);

    /**
     * remove a resource.
     * doesn't return anything
     *
     * @param AbstractResource $res
     */
    public function delete(AbstractResource $res);

    /**
     * defaultAction when the user click on a response. It should render a response.
     *
     * @param integer $resourceId
     */
    public function getDefaultAction($resourceId);

    /**
     * indexAction when the user click on a response. It should render a response.
     * note: the resource controller wich redirects to this manager method is the openAction() one.
     *
     * @param integer $workspaceId
     * @param integer $resourceId
     */
    public function indexAction($workspaceId, $resourceId);

    /**
     * copy a resource. $resource should have AbstractResource sub type.
     *
     * @param AbstractResource $resource the resource copied
     * @param User $user the user copying
     */
    public function copy($resource, User $user);

    /**
     * renders the edit form
     *
     * @param integer $resourceId
     */
    public function editFormPageAction($resourceId);
}