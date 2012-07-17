<?php

namespace Claroline\CoreBundle\Library\Installation\Plugin;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BaseCheckerTest extends WebTestCase
{
    /** @var CommonChecker */
    private $checker;

    /** @var Loader */
    private $loader;

    protected function setUp()
    {
        $container = static::createClient()->getContainer();
        $this->checker = $container->get('claroline.plugin.base_checker');
        $pluginDirectory = $container->getParameter('claroline.stub_plugin_directory');
        $this->loader = new Loader($pluginDirectory);
    }

    /**
     * @dataProvider invalidFqcnProvider
     */
    public function testCheckerReturnsAnErrorOnInvalidFqcn($pluginFqcn)
    {
        $errors = $this->checker->check($this->loader->load($pluginFqcn));
        $this->assertEquals(BaseChecker::INVALID_FQCN, $errors[0]->getCode());
    }

    /**
     * @dataProvider unexpectedTranslationKeyProvider
     */
    public function testCheckerReturnsAnErrorOnInvalidTranslationKey($pluginFqcn)
    {
        $errors = $this->checker->check($this->loader->load($pluginFqcn));
        $this->assertEquals(BaseChecker::INVALID_TRANSLATION_KEY, $errors[0]->getCode());
    }

    public function invalidFqcnProvider()
    {
        return array(
            array('Invalid\NonConventionalFQCN1\AdditionalNamespaceSegment\InvalidNonConventionalFQCN1'),
            array('Invalid\NonConventionalFQCN2\UnexpectedBundleClassName')
        );
    }

    public function unexpectedTranslationKeyProvider()
    {
        return array(
            array('Invalid\UnexpectedTranslationKey1\InvalidUnexpectedTranslationKey1'),
            array('Invalid\UnexpectedTranslationKey2\InvalidUnexpectedTranslationKey2'),
            array('Invalid\UnexpectedTranslationKey3\InvalidUnexpectedTranslationKey3'),
            array('Invalid\UnexpectedTranslationKey4\InvalidUnexpectedTranslationKey4')
        );
    }
}