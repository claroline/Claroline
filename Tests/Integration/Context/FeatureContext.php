<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Tests\Integration\Context;

use Behat\Behat\Exception\PendingException;
use Symfony\Component\HttpKernel\KernelInterface;
use Behat\Symfony2Extension\Context\KernelAwareInterface;
use Behat\MinkExtension\Context\MinkContext;
use Claroline\CoreBundle\Library\Installation\Settings\SettingChecker;

/**
 * Feature context.
 */
class FeatureContext extends MinkContext implements KernelAwareInterface
{
    private $kernel;
    private $parameters;

    /**
     * Initializes context with parameters from behat.yml.
     *
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * Sets HttpKernel instance.
     * This method will be automatically called by Symfony2Extension ContextInitializer.
     *
     * @param KernelInterface $kernel
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    public function getContainer()
    {
        return $this->kernel->getContainer();
    }

    /******************/
    /* Initialization */
    /******************/
    /**
     * @Given /^the platform is initialized$/
     */
    public function thePlatformIsInitialized()
    {
        $this->visit($this->getBaseUrl() . '/app_dev.php/dev/reinstall');
    }

    /**
     * @Given /^the database does not exists$/
     */
    public function theDatabaseIsEmpty()
    {
        $cn = $this->getContainer()->get('doctrine.dbal.default_connection');
        $exists = true;
        try {
            $cn->query('SELECT 1');
        } catch (\Doctrine\DBAL\Exception\AccessDeniedException $ex) {
            $exists = false;
        }

        if ($exists) {
            throw new \Exception('The database already exists and must be dropped');
        }
    }

    /**
     * @Given /^operation\.xml is initialized$/
     */
    public function operationXmlIsInitialized()
    {
        $ds = DIRECTORY_SEPARATOR;
        $operationFile = $this->kernel->getRootDir() . $ds . 'config' . $ds . 'operations.xml';

        if (!file_exists($operationFile)) {
            file_put_contents(
                $operationFile,
                '<operations><install type="core">Claroline\CoreBundle\ClarolineCoreBundle</install></operations>'
            );
        }
    }

    /**
     * @Given /^installation directories are writable$/
     */
    public function installationDirectoriesAreWritable()
    {
        $checker = new SettingChecker();
        //var_dump($checker->getSettingCategories());
        //hasFailedRequirequirement() always return false. It's a problem.

        if ($checker->hasFailedRequirement()) {
            //todo show the directory list
            throw new \Exception('Failed requirements');
        }
    }

    /**
     * @Given /^the cache directory is writable$/
     */
    public function theCacheDirectoryIsWritable()
    {
        //It doesn't work
        //throw new \Behat\Behat\Exception\PendingException('Does not work');

        $dir = $this->kernel->getRootDir() . '/cache/prod/jms_diextra/metadata';
        $res = is_writeable($dir);

        if (!$res) {
            throw new \Exception('The cache directory is not writable');
        }
    }

    /**
     * @Given /^I fill in "([^"]*)" with database name$/
     */
    public function iFillInWithDatabaseName($field)
    {
        $this->fillField($field, 'claroline_prod');
    }

    /**
     * @Given /^I fill in "([^"]*)" with database username$/
     */
    public function iFillInWithDatabaseUsername($field)
    {
        $this->fillField($field, 'root');
    }

    /**
     * @Given /^I fill in "([^"]*)" with database password$/
     */
    public function iFillInWithDatabasePassword($field)
    {
        $this->fillField($field, 'vanille');
    }


    /**
     * @Given /^self registration is allowed$/
     */
    public function selfRegistrationIsAllowed()
    {
        $configHandler = $this->getContainer()->get('claroline.config.platform_config_handler');
        $configHandler->setParameters(array('allow_self_registration' => true));
    }

    /**
     * @Given /^self registration is disabled$/
     */
    public function selfRegistrationIsDisabled()
    {
        $configHandler = $this->getContainer()->get('claroline.config.platform_config_handler');
        $configHandler->setParameters(array('allow_self_registration' => false));
    }

    /************/
    /* Fixtures */
    /************/

    /**
     * @Given /^the user "([^"]*)" is created$/
     */
    public function theUserIsCreated($username)
    {
        $this->visit($this->getBaseUrl() . "/app_dev.php/dev/user/create/{$username}/ROLE_ADMIN");
    }

    /**
     * @Given /^the group "([^"]*)" is created$/
     */
    public function theGroupIsCreated($name)
    {
        $this->visit($this->getBaseUrl() . "/app_dev.php/dev/group/create/{$name}");
    }

    /**
     * @Given /^the workspace "([^"]*)" is created by "([^"]*)"$/
     */
    public function theWorkspaceIsCreatedBy($workspaceName, $username)
    {
        $this->visit($this->getBaseUrl() . "/app_dev.php/dev/workspace/create/{$workspaceName}/{$username}");
    }

    /***********/
    /* Actions */
    /***********/

    /**
     * @When /^I follow the hidden "([^"]*)"$/
     */
    public function iFollowTheHidden($label)
    {
        $script = "(function() { $('a:contains(\"{$label}\")')[0].click(); })();";
        $this->getSession()->evaluateScript($script);
    }

    /**
     * @Given /^I check the "([^"]*)" line$/
     */
    public function iCheckTheLine($text)
    {
        throw new PendingException();
        /*
        $script = "(function() {
            var row = $('tr:contains(\"{$text}\")');
            var checkbox = $('#' + row.attr('id') + 'td input:checkbox');
        })();";
        $this->getSession()->evaluateScript($script);*/
    }


    /**************/
    /* Assertions */
    /**************/

    /**
     * @Then /^the platform should have "([^"]*)" "([^"]*)"$/
     */
    public function thePlatformShouldHave($count, $entity)
    {
        $res = $this->getContainer()->get('claroline.persistence.object_manager')
            ->count('ClarolineCoreBundle:' . $entity);

        if ($res != $count) {
            throw new \Exception('The plateform has ' . $res . ' ' . $entity);
        }
    }

    /**
     * @Then /^database should exists$/
     */
    public function databaseShouldExists()
    {
        $cn = $this->getContainer()->get('doctrine.dbal.default_connection');
        $cn->query('SELECT 1');
    }

    /**
     * @Then /^user "([^"]*)" should exists$/
     */
    public function userShouldExists($username)
    {
        throw new \Behat\Behat\Exception\PendingException('This assertion does not work. The table never exists');
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $em->getRepository('ClarolineCoreBundle:User')->findOneByUsername($username);
    }

    /**********/
    /* OTHERS */
    /**********/

    /**
     * @Given /^base url is web$/
     */
    public function baseUrlIsWeb()
    {
        $this->setMinkParameter('base_url', $this->getBaseUrl());
    }

    private function getBaseUrl()
    {
        return str_replace('app.php/', '', $this->getMinkParameter('base_url'));
    }
}
