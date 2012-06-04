<?php
namespace Claroline\CoreBundle\Library\Manager;

interface ResourceInterface
{
    public function getResourceType();
    public function getForm();
    public function add($form, $id, $user);
    public function delete($rsrc);
    public function getDefaultAction($id);
    public function indexAction($workspaceId, $id);
    public function copy($resource, $user);
}