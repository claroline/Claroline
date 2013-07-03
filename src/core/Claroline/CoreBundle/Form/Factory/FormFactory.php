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

        if (!$entityVar) {
            $entityVar = new self::$types[$type]['entity'];
        }

        return $this->factory->create($formType, $entityVar);
    }
}