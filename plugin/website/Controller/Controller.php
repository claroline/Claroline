<?php
/**
 * Created by PhpStorm.
 * User: panos
 * Date: 7/4/14
 * Time: 4:00 PM.
 */

namespace Icap\WebsiteBundle\Controller;

use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Icap\WebsiteBundle\Entity\Website;
use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class Controller extends BaseController
{
    /**
     * @param string  $permission
     * @param Website $website
     *
     * @throws AccessDeniedException
     */
    protected function checkAccess($permission, Website $website)
    {
        $collection = new ResourceCollection([$website->getResourceNode()]);
        if (!$this->get('security.authorization_checker')->isGranted($permission, $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }
    }

    /**
     * @param string  $permission
     * @param Website $website
     *
     * @return bool
     */
    protected function isUserGranted($permission, Website $website, $collection = null)
    {
        if (null === $collection) {
            $collection = new ResourceCollection([$website->getResourceNode()]);
        }
        $checkPermission = false;
        if ($this->get('security.authorization_checker')->isGranted($permission, $collection)) {
            $checkPermission = true;
        }

        return $checkPermission;
    }

    /**
     * @param $event
     *
     * @return Controller
     */
    protected function dispatch($event)
    {
        $this->get('event_dispatcher')->dispatch('log', $event);

        return $this;
    }

    /**
     * @param Website $website
     * @param string  $childType
     * @param string  $action
     * @param array   $details
     *
     * @return Controller
     */
    protected function dispatchChildEvent(Website $website, $childType, $action, $details = [])
    {
        $event = new LogResourceChildUpdateEvent(
            $website->getResourceNode(),
            $childType,
            $action,
            $details
        );

        return $this->dispatch($event);
    }

    /**
     * Retrieve logged user. If anonymous return null.
     *
     * @return user
     */
    protected function getLoggedUser()
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        if (is_string($user)) {
            $user = null;
        }

        return $user;
    }

    /**
     * @return \Icap\WebsiteBundle\Manager\WebsitePageManager
     */
    protected function getWebsitePageManager()
    {
        return $this->get('icap.website.page.manager');
    }

    /**
     * @return \Icap\WebsiteBundle\Repository\WebsitePageRepository
     */
    protected function getWebsitePageRepository()
    {
        return $this->get('icap_website.repository.page');
    }

    /**
     * @return \Icap\WebsiteBundle\Manager\WebsiteOptionsManager
     */
    protected function getWebsiteOptionsManager()
    {
        return $this->get('icap.website.options.manager');
    }
}
