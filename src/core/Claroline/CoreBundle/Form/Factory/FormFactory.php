<?php

namespace Claroline\CoreBundle\Form\Factory;

use Symfony\Component\Form\FormFactoryInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.form.factory")
 */
class FormFactory
{
    const TYPE_MESSAGE = 'message';
    const TYPE_ORDERED_TOOL = 'ordered_tool';
    const TYPE_TOOL = 'tool';
    const TYPE_USER = 'user';
    const TYPE_GROUP = 'group';
    const TYPE_GROUP_SETTINGS = 'group_settings';
    const TYPE_PLATFORM_PARAMETERS = 'platform_parameters';
    const TYPE_USER_IMPORT = 'user_import';
    const TYPE_ADMIN_ANALYTICS_CONNECTIONS = 'admin_analytics_connections';
    const TYPE_ADMIN_ANALYTICS_TOP = 'admin_analytics_top';
    const TYPE_MAIL = 'mail';
    const TYPE_WORKSPACE = 'workspace';
    const TYPE_WORKSPACE_EDIT = 'workspace_edit';
    const TYPE_WORKSPACE_TEMPLATE = 'workspace_template';
    const TYPE_LOG_WORKSPACE_WIDGET_CONFIG = 'log_workspace_widget_config';
    const TYPE_LOG_DESKTOP_WIDGET_CONFIG = 'log_desktop_widget_config';
    const TYPE_CALENDAR = 'calendar';
    const TYPE_WORKSPACE_TAG = 'workspace_tag';
    const TYPE_ADMIN_WORKSPACE_TAG = 'admin_workspace_tag';
    const TYPE_RESOURCE_PERMS = 'resource_permissions';
    const TYPE_RESOURCE_RENAME = 'resource_rename';
    const TYPE_RESOURCE_PROPERTIES = 'resource_properties';
    const TYPE_WORKSPACE_ROLE = 'workspace_role';
    const TYPE_ROLE_TRANSLATION = 'workspace_role_translation';

    private static $types = array(
        self::TYPE_MESSAGE => array(
            'formType' => 'Claroline\CoreBundle\Form\MessageType',
            'entity' => 'Claroline\CoreBundle\Entity\Message'
        ),
        self::TYPE_ORDERED_TOOL => array(
            'formType' => 'Claroline\CoreBundle\Form\WorkspaceOrderToolEditType',
            'entity' => 'Claroline\CoreBundle\Entity\Tool\OrderedTool'
        ),
        self::TYPE_TOOL => array(
            'formType' => 'Claroline\CoreBundle\Form\ToolType',
            'entity' => 'Claroline\CoreBundle\Entity\Tool\Tool'
        ),
        self::TYPE_USER => array(
            'formType' => 'Claroline\CoreBundle\Form\ProfileType',
            'entity' => 'Claroline\CoreBundle\Entity\User'
        ),
        self::TYPE_GROUP => array(
            'formType' => 'Claroline\CoreBundle\Form\GroupType',
            'entity' => 'Claroline\CoreBundle\Entity\Group'
        ),
        self::TYPE_GROUP_SETTINGS => array(
            'formType' => 'Claroline\CoreBundle\Form\GroupSettingsType',
            'entity' => 'Claroline\CoreBundle\Entity\Group'
        ),
        self::TYPE_PLATFORM_PARAMETERS => array(
            'formType' => 'Claroline\CoreBundle\Form\PlatformParametersType',
            'entity' => 'Claroline\CoreBundle\Library\Configuration\PlatformConfiguration'
        ),
        self::TYPE_USER_IMPORT => array(
            'formType' => 'Claroline\CoreBundle\Form\ImportUserType'
        ),
        self::TYPE_ADMIN_ANALYTICS_CONNECTIONS => array(
            'formType' => 'Claroline\CoreBundle\Form\AdminAnalyticsConnectionsType'
        ),
        self::TYPE_ADMIN_ANALYTICS_TOP => array(
            'formType' => 'Claroline\CoreBundle\Form\AdminAnalyticsTopType'
        ),
        self::TYPE_MAIL => array(
            'formType' => 'Claroline\CoreBundle\Form\MailType'
        ),
        self::TYPE_WORKSPACE => array(
            'formType' => 'Claroline\CoreBundle\Form\WorkspaceType'
        ),
        self::TYPE_WORKSPACE_EDIT => array(
            'formType' => 'Claroline\CoreBundle\Form\WorkspaceEditType',
            'entity' => 'Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace'
        ),
        self::TYPE_WORKSPACE_TEMPLATE => array(
            'formType' => 'Claroline\CoreBundle\Form\WorkspaceTemplateType'
        ),
        self::TYPE_LOG_WORKSPACE_WIDGET_CONFIG => array(
            'formType' => 'Claroline\CoreBundle\Form\LogWorkspaceWidgetConfigType'
        ),
        self::TYPE_LOG_DESKTOP_WIDGET_CONFIG => array(
            'formType' => 'Claroline\CoreBundle\Form\LogDesktopWidgetConfigType'
        ),
        self::TYPE_CALENDAR => array(
            'formType' => 'Claroline\CoreBundle\Form\CalendarType',
            'entity' => 'Claroline\CoreBundle\Entity\Event'
        ),
        self::TYPE_WORKSPACE_TAG => array(
            'formType' => 'Claroline\CoreBundle\Form\WorkspaceTagType',
            'entity' => 'Claroline\CoreBundle\Entity\Workspace\WorkspaceTag'
        ),
        self::TYPE_ADMIN_WORKSPACE_TAG => array(
            'formType' => 'Claroline\CoreBundle\Form\AdminWorkspaceTagType',
            'entity' => 'Claroline\CoreBundle\Entity\Workspace\WorkspaceTag'
        ),
        self::TYPE_RESOURCE_PERMS => array(
            'formType' => 'Claroline\CoreBundle\Form\ResourceRightType',
            'entity' => 'Claroline\CoreBundle\Entity\Resource\ResourceRights'
        ),
        self::TYPE_RESOURCE_RENAME => array(
            'formType' => 'Claroline\CoreBundle\Form\ResourceNameType',
            'entity' => 'Claroline\CoreBundle\Entity\Resource\AbstractResource'
        ),
        self::TYPE_RESOURCE_PROPERTIES => array(
            'formType' => 'Claroline\CoreBundle\Form\ResourcePropertiesType',
            'entity' => 'Claroline\CoreBundle\Entity\Resource\AbstractResource'
        ),
        self::TYPE_WORKSPACE_ROLE => array(
            'formType' => 'Claroline\CoreBundle\Form\WorkspaceRoleType'
        ),
        self::TYPE_ROLE_TRANSLATION => array(
            'formType' => 'Claroline\CoreBundle\Form\RoleTranslationType',
            'entity' => 'Claroline\CoreBundle\Entity\Role'
        )
    );

    private $factory;

    /**
     * @DI\InjectParams({
     *     "factory" = @DI\Inject("form.factory")
     * })
     */
    public function __construct(FormFactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    public function create($type, array $typeArgs = array(), $entityVar = null)
    {
        if (!isset(self::$types[$type])) {
            throw new UnknownTypeException(
                "Unknown form type '{$type}' : type must be a TYPE_* class constant"
            );
        }

        if (count($typeArgs) > 0) {
            $rType = new \ReflectionClass(self::$types[$type]['formType']);
            $formType = $rType->newInstanceArgs($typeArgs);
        } else {
            $formType = new self::$types[$type]['formType'];
        }

        if (!$entityVar && isset(self::$types[$type]['entity'])) {
            $entityVar = new self::$types[$type]['entity'];
        }

        return $this->factory->create($formType, $entityVar);
    }
}
