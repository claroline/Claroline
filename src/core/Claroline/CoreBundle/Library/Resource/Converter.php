<?php

namespace Claroline\CoreBundle\Library\Resource;

use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Library\Security\Utilities as SecurityUtilities;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.resource.converter")
 */
class Converter
{
    /* @var EntityManager */
    private $em;
    private $ut;
    private $translator;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "ut" = @DI\Inject("claroline.security.utilities"),
     *     "translator" = @DI\Inject("translator")
     * })
     */
    public function __construct(EntityManager $em, SecurityUtilities $ut, $translator)
    {
        $this->em = $em;
        $this->ut = $ut;
        $this->translator = $translator;
    }

    /**
     * Convert a ressource into an array (mainly used to be serialized and sent to the manager.js as
     * a json response)
     *
     * @param \Claroline\CoreBundle\Entity\Resource\AbstractResource $resource
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     *
     * @return array
     */
    public function toArray(AbstractResource $resource, TokenInterface $token)
    {
        $resourceArray = array();
        $resourceArray['id'] = $resource->getId();
        $resourceArray['name'] = $resource->getName();
        $resourceArray['parent_id'] = ($resource->getParent() != null) ? $resource->getParent()->getId() : null;
        $resourceArray['creator_username'] = $resource->getCreator()->getUsername();
        $resourceArray['type'] = $resource->getResourceType()->getName();
        $resourceArray['is_browsable'] = $resource->getResourceType()->getBrowsable();
        $resourceArray['large_icon'] = $resource->getIcon()->getRelativeUrl();
        $resourceArray['path_for_display'] = $resource->getPathForDisplay();

        $isAdmin = false;

        $roles = $this->ut->getRoles($token);

        foreach ($roles as $role) {
            if ($role === 'ROLE_ADMIN') {
                $isAdmin = true;
            }
        }

        if ($isAdmin) {
            $resourceArray['can_export'] = true;
            $resourceArray['can_edit'] = true;
            $resourceArray['can_delete'] = true;
        } else {
            $rights = $this->em
                ->getRepository('ClarolineCoreBundle:Resource\ResourceRights')
                ->findMaximumRights($roles, $resource);
            $resourceArray['can_export'] = $rights['canExport'];
            $resourceArray['can_edit'] = $rights['canEdit'];
            $resourceArray['can_delete'] = $rights['canDelete'];
        }

        return $resourceArray;
    }

    /**
     * Convert a ressource into an json string (mainly used to be sent to the manager.js)
     *
     * @param \Claroline\CoreBundle\Entity\Resource\AbstractResource $resource
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     *
     * @return array
     *
     * @todo this method shouldn't wrap the converted resource in an additional array
     */
    public function toJson(AbstractResource $resource, TokenInterface $token)
    {
        $phpArray[0] = $this->toArray($resource, $token);
        $json = json_encode($phpArray);

        return $json;
    }

    public function jsonEncodeGroups($groups)
    {
        $content = array();

        for ($i = 0, $size = count($groups); $i < $size; $i++) {
            $content[$i]['id'] = $groups[$i]->getId();
            $content[$i]['name'] = $groups[$i]->getName();
            $rolesString = '';
            $roles = $groups[$i]->getEntityRoles();

            for ($j = 0, $rolesCount = count($roles); $j < $rolesCount; $j++) {
                $rolesString .= "{$this->translator->trans($roles[$j]->getTranslationKey(), array(), 'platform')}";
                if ($j <= $rolesCount - 2) {
                    $rolesString .= ' ,';
                }
            }

            $content[$i]['roles'] = $rolesString;

        }

        return json_encode($content);
    }

    public function jsonEncodeUsers($users)
    {
        $content = array();

        for ($i = 0, $size = count($users); $i < $size; $i++) {
            $content[$i]['id'] = $users[$i]->getId();
            $content[$i]['username'] = $users[$i]->getUsername();
            $content[$i]['lastname'] = $users[$i]->getLastName();
            $content[$i]['firstname'] = $users[$i]->getFirstName();
            $content[$i]['administrativeCode'] = $users[$i]->getAdministrativeCode();

            $rolesString = '';
            $roles = $users[$i]->getEntityRoles();

            for ($j = 0, $rolesCount = count($roles); $j < $rolesCount; $j++) {
                $rolesString .= "{$this->translator->trans($roles[$j]->getTranslationKey(), array(), 'platform')}";
                if ($j <= $rolesCount - 2) {
                    $rolesString .= ' ,';
                }
            }

            $content[$i]['roles'] = $rolesString;
        }

        return json_encode($content);
    }
}