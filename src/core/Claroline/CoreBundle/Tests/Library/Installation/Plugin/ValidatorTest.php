<?php

namespace Claroline\CoreBundle\Library\Installation\Plugin;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ValidatorTest extends WebTestCase
{
    public function testValidatorAcceptsOnlyInstancesOfCheckerInterface()
    {
        $this->setExpectedException('InvalidArgumentException');
        $checkers = array(
            'regular' => $this->getMock('Claroline\CoreBundle\Library\Installation\Plugin\CheckerInterface'),
            'wrong' => new \stdClass()
        );

        new Validator($checkers);
    }

    public function testValidatorCollectsValidationErrorsFromCheckers()
    {
        $firstChecker = $this->getMock('Claroline\CoreBundle\Library\Installation\Plugin\CheckerInterface');
        $secondChecker = $this->getMock('Claroline\CoreBundle\Library\Installation\Plugin\CheckerInterface');
        $thirdChecker = $this->getMock('Claroline\CoreBundle\Library\Installation\Plugin\CheckerInterface');
        $plugin = $this->getMock('Claroline\CoreBundle\Library\PluginBundle');

        $firstError = new ValidationError('foo');
        $secondError = new ValidationError('bar');
        $thirdError = new ValidationError('baz');

        $firstChecker->expects($this->once())
            ->method('check')
            ->with($plugin)
            ->will($this->returnValue(array()));
        $secondChecker->expects($this->once())
            ->method('check')
            ->with($plugin)
            ->will($this->returnValue(array($firstError)));
        $thirdChecker->expects($this->once())
            ->method('check')
            ->with($plugin)
            ->will($this->returnValue(array($secondError, $thirdError)));

        $validator = new Validator(array($firstChecker, $secondChecker, $thirdChecker));
        $errors = $validator->validate($plugin);

        $this->assertEquals(array($firstError, $secondError, $thirdError), $errors);
    }

    /**
     * @dataProvider validPluginProvider
     */
    public function testValidatorReturnsNoErrorForValidPlugins($pluginFqcn)
    {
        $ds = DIRECTORY_SEPARATOR;
        require_once __DIR__."{$ds}..{$ds}..{$ds}..{$ds}Stub{$ds}plugin{$ds}Valid{$ds}"
            . "WithCustomResources{$ds}Entity{$ds}ResourceA.php";
        require_once __DIR__."{$ds}..{$ds}..{$ds}..{$ds}Stub{$ds}plugin{$ds}Valid{$ds}"
            . "WithCustomResources{$ds}Entity{$ds}ResourceB.php";
        require_once __DIR__."{$ds}..{$ds}..{$ds}..{$ds}Stub{$ds}plugin{$ds}Valid{$ds}"
            . "WithCustomActions{$ds}Entity{$ds}ResourceX.php";

        $container = static::createClient()->getContainer();
        $validator = $container->get('claroline.plugin.validator');
        $pluginDirectory = $container->getParameter('claroline.param.stub_plugin_directory');
        $loader = new Loader($pluginDirectory);
        $plugin = $loader->load($pluginFqcn);
        $errors = $validator->validate($plugin);

        $this->assertEquals(0, count($errors));
    }

    public function validPluginProvider()
    {
        return array(
            array('Valid\Minimal\ValidMinimal'),
            array('Valid\Simple\ValidSimple'),
            array('Valid\Custom\ValidCustom'),
            array('Valid\WithMigrations\ValidWithMigrations'),
            array('Valid\WithCustomResources\ValidWithCustomResources')
        );
    }
}