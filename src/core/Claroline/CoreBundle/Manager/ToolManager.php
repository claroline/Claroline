<?php

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Writer\ToolWriter;

/**
 * @DI\Service("claroline.manager.tool_manager")
 */
class ToolManager
{
    /** @var ToolWriter */
    private $writer;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "writer" = @DI\Inject("claroline.writer.tool_writer"),
     * })
     */
    public function __construct(ToolWriter $writer)
    {
        $this->writer = $writer;
    }

    public function create()
    {
        //do something
    }
}