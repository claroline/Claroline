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