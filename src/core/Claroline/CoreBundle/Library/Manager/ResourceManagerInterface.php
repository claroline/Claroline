<?php

namespace Claroline\CoreBundle\Library\Manager;

/*
 * is implemented by resources manager. It describes methods wich will be used 
 * for each Resources by the Claroline\CoreBundle\Controller\ResourceController
 */
interface ResourceManagerInterface
{
    /*
     * Each manager works for a different entity Resource\ResourceTye.
     * Returns the ResourceType->getType() value.
     */
    public function getResourceType();
    
    /*
     * Returns the FormFactory->create(entity) value.
     */
    public function getForm();
    
    /*
     * Returns the form page.
     * 
     * @param $twigFile the twig file rendered
     * @param $id the resource parent id
     * @param $type the ResourceType->getType() value the form will work with
     */
    public function getFormPage($twigFile, $id, $type);
    
    //todo: remove $id from add.
    /*
     * Return the Resource entity added.
     * 
     * @param $form the form containing usefull datas
     * @param $id the ResourceInstance parent. It may not be usefull.
     * @param $user the user adding the new resource. It may not be usefull. 
     */
    public function add($form, $id, $user);
    
    /*
     * remove a resource.
     * doesn't return anything
     */
    public function delete($res);
    
    /*
     * defaultAction when the user click on a response. It should render a response.
     */
    public function getDefaultAction($id);
    
    /*
     * indexAction when the user click on a response. It should render a response.
     * note: the resource controller wich redirects to this manager method is the openAction() one.
     * 
     * @param $id the resource id.
     */
    public function indexAction($workspaceId, $id);
    
    /*
     * copy a resource
     * 
     * @param $resource the resource copied
     * @param $user the user copying
     */
    public function copy($resource, $user);
}