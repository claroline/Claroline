<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form\Factory;

use Symfony\Component\Form\FormFactoryInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.form.factory")
 */
class FormFactory
{
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
    const TYPE_EMAIL = 'email';
    const TYPE_WORKSPACE = 'workspace';
    const TYPE_WORKSPACE_EDIT = 'workspace_edit';
    const TYPE_WORKSPACE_IMPORT = 'workspace_import';
    const TYPE_LOG_WORKSPACE_WIDGET_CONFIG = 'log_workspace_widget_config';
    const TYPE_LOG_DESKTOP_WIDGET_CONFIG = 'log_desktop_widget_config';
    const TYPE_AGENDA = 'agenda';
    const TYPE_WORKSPACE_TAG = 'workspace_tag';
    const TYPE_ADMIN_WORKSPACE_TAG = 'admin_workspace_tag';
    const TYPE_RESOURCE_PERMS = 'resource_permissions';
    const TYPE_RESOURCE_RENAME = 'resource_rename';
    const TYPE_RESOURCE_PROPERTIES = 'resource_properties';
    const TYPE_WORKSPACE_ROLE = 'workspace_role';
    const TYPE_ROLE_TRANSLATION = 'workspace_role_translation';
    const TYPE_USER_EMAIL = 'user_email';
    const TYPE_USER_RESET_PWD = 'user_reset_pwd';
    const TYPE_SIMPLE_TEXT = 'simple_text';
    const TYPE_HOME_TAB = 'home_tab';
    const TYPE_WIDGET_CONFIG = 'widget_config';
    const TYPE_WIDGET_INSTANCE = 'widget_instance';
    const TYPE_RESOURCE_TEXT = 'resource_text';
    const TYPE_USER_FULL = 'user_full';
    const TYPE_USER_BASE_PROFILE = 'user_base_profile';
    const TYPE_PLATFORM_APPEARANCE = 'platform_appearance';
    const TYPE_PLATFORM_MAIL_SERVER = 'platform_mail_server';
    const TYPE_PLATFORM_MAIL_INSCRIPTION = 'platform_mail_inscription';
    const TYPE_AGENDA_IMPORTER = 'import_agenda_file';
    const TYPE_IMPORT_USERS_IN_GROUP = 'import_users_in_group';

    private static $types = array(
        self::TYPE_ORDERED_TOOL => array(
            'formType' => 'Claroline\CoreBundle\Form\WorkspaceOrderToolEditType',
            'entity' => 'Claroline\CoreBundle\Entity\Tool\OrderedTool',
        ),
        self::TYPE_TOOL => array(
            'formType' => 'Claroline\CoreBundle\Form\ToolType',
            'entity' => 'Claroline\CoreBundle\Entity\Tool\Tool',
        ),
        self::TYPE_USER => array(
            'formType' => 'Claroline\CoreBundle\Form\ProfileType',
            'entity' => 'Claroline\CoreBundle\Entity\User',
        ),
        self::TYPE_GROUP => array(
            'formType' => 'Claroline\CoreBundle\Form\GroupType',
            'entity' => 'Claroline\CoreBundle\Entity\Group',
        ),
        self::TYPE_GROUP_SETTINGS => array(
            'formType' => 'Claroline\CoreBundle\Form\GroupSettingsType',
            'entity' => 'Claroline\CoreBundle\Entity\Group',
        ),
        self::TYPE_PLATFORM_PARAMETERS => array(
            'formType' => 'Claroline\CoreBundle\Form\Administration\GeneralType',
            'entity' => 'Claroline\CoreBundle\Library\Configuration\PlatformConfiguration',
        ),
        self::TYPE_USER_IMPORT => array(
            'formType' => 'Claroline\CoreBundle\Form\ImportUserType',
        ),
        self::TYPE_IMPORT_USERS_IN_GROUP => array(
            'formType' => 'Claroline\CoreBundle\Form\ImportUsersInGroupType',
        ),
        self::TYPE_ADMIN_ANALYTICS_CONNECTIONS => array(
            'formType' => 'Claroline\CoreBundle\Form\AdminAnalyticsConnectionsType',
        ),
        self::TYPE_ADMIN_ANALYTICS_TOP => array(
            'formType' => 'Claroline\CoreBundle\Form\AdminAnalyticsTopType',
        ),
        self::TYPE_MAIL => array(
            'formType' => 'Claroline\CoreBundle\Form\MailType',
        ),
        self::TYPE_WORKSPACE => array(
            'formType' => 'Claroline\CoreBundle\Form\WorkspaceType',
        ),
        self::TYPE_WORKSPACE_EDIT => array(
            'formType' => 'Claroline\CoreBundle\Form\WorkspaceEditType',
            'entity' => 'Claroline\CoreBundle\Entity\Workspace\Workspace',
        ),
        self::TYPE_WORKSPACE_IMPORT => array(
            'formType' => 'Claroline\CoreBundle\Form\ImportWorkspaceType',
        ),
        self::TYPE_LOG_WORKSPACE_WIDGET_CONFIG => array(
            'formType' => 'Claroline\CoreBundle\Form\Log\LogWorkspaceWidgetConfigType',
        ),
        self::TYPE_LOG_DESKTOP_WIDGET_CONFIG => array(
            'formType' => 'Claroline\CoreBundle\Form\Log\LogDesktopWidgetConfigType',
        ),
        self::TYPE_AGENDA => array(
            'formType' => 'Claroline\CoreBundle\Form\AgendaType',
            'entity' => 'Claroline\CoreBundle\Entity\Event',
        ),
        self::TYPE_WORKSPACE_TAG => array(
            'formType' => 'Claroline\CoreBundle\Form\WorkspaceTagType',
            'entity' => 'Claroline\CoreBundle\Entity\Workspace\WorkspaceTag',
        ),
        self::TYPE_ADMIN_WORKSPACE_TAG => array(
            'formType' => 'Claroline\CoreBundle\Form\AdminWorkspaceTagType',
            'entity' => 'Claroline\CoreBundle\Entity\Workspace\WorkspaceTag',
        ),
        self::TYPE_RESOURCE_PERMS => array(
            'formType' => 'Claroline\CoreBundle\Form\ResourceRightType',
            'entity' => 'Claroline\CoreBundle\Entity\Resource\ResourceRights',
        ),
        self::TYPE_RESOURCE_RENAME => array(
            'formType' => 'Claroline\CoreBundle\Form\ResourceNameType',
            'entity' => 'Claroline\CoreBundle\Entity\Resource\ResourceNode',
        ),
        self::TYPE_RESOURCE_PROPERTIES => array(
            'formType' => 'Claroline\CoreBundle\Form\ResourcePropertiesType',
            'entity' => 'Claroline\CoreBundle\Entity\Resource\ResourceNode',
        ),
        self::TYPE_WORKSPACE_ROLE => array(
            'formType' => 'Claroline\CoreBundle\Form\WorkspaceRoleType',
        ),
        self::TYPE_ROLE_TRANSLATION => array(
            'formType' => 'Claroline\CoreBundle\Form\RoleTranslationType',
        ),
        self::TYPE_USER_EMAIL => array(
            'formType' => 'Claroline\CoreBundle\Form\EmailType',
        ),
        self::TYPE_USER_RESET_PWD => array(
            'formType' => 'Claroline\CoreBundle\Form\ResetPasswordType',
            'entity' => 'Claroline\CoreBundle\Entity\User',
        ),
        self::TYPE_SIMPLE_TEXT => array(
            'formType' => 'Claroline\CoreBundle\Form\SimpleTextType',
        ),
        self::TYPE_HOME_TAB => array(
            'formType' => 'Claroline\CoreBundle\Form\HomeTabType',
            'entity' => 'Claroline\CoreBundle\Entity\Home\HomeTab',
        ),
        self::TYPE_WIDGET_CONFIG => array(
            'formType' => 'Claroline\CoreBundle\Form\WidgetDisplayType',
            'entity' => 'Claroline\CoreBundle\Entity\Widget\WidgetInstance',
        ),
        self::TYPE_WIDGET_INSTANCE => array(
            'formType' => 'Claroline\CoreBundle\Form\WidgetInstanceType',
            'entity' => 'Claroline\CoreBundle\Entity\Widget\WidgetInstance',
        ),
        self::TYPE_RESOURCE_TEXT => array(
            'formType' => 'Claroline\CoreBundle\Form\TextType',
            'entity' => 'Claroline\CoreBundle\Entity\Resource\Text',
        ),
        self::TYPE_EMAIL => array(
            'formType' => 'Claroline\CoreBundle\Form\SendMailType',
        ),
        self::TYPE_USER_FULL => array(
            'formType' => 'Claroline\CoreBundle\Form\ProfileCreationType',
            'entity' => 'Claroline\CoreBundle\Entity\User',
        ),
        self::TYPE_USER_BASE_PROFILE => array(
            'formType' => 'Claroline\CoreBundle\Form\BaseProfileType',
            'entity' => 'Claroline\CoreBundle\Entity\User',
        ),
        self:: TYPE_PLATFORM_APPEARANCE => array(
            'formType' => 'Claroline\CoreBundle\Form\Administration\AppearanceType',
        ),
        self:: TYPE_PLATFORM_MAIL_SERVER => array(
            'formType' => 'Claroline\CoreBundle\Form\Administration\MailServerType',
        ),
        self:: TYPE_PLATFORM_MAIL_INSCRIPTION => array(
            'formType' => 'Claroline\CoreBundle\Form\Administration\MailInscriptionType',
        ),
        self:: TYPE_AGENDA_IMPORTER => array(
            'formType' => 'Claroline\CoreBundle\Form\ImportAgendaType',
        ),
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
            $formType = new self::$types[$type]['formType']();
        }

        if (!$entityVar && isset(self::$types[$type]['entity'])) {
            $entityVar = new self::$types[$type]['entity']();
        }

        return $this->factory->create($formType, $entityVar);
    }

    public function createFormBuilder($data = null, array $options = array())
    {
        return $this->factory->createBuilder('form', $data, $options);
    }
}
