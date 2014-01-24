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

use Behat\Behat\Context\Step;
use Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Exception\ExpectationException;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Behat\MinkExtension\Context\MinkContext;
use Claroline\CoreBundle\Library\Installation\Settings\SettingChecker;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Feature context.
 */
class FeatureContext extends MinkContext
{
    use KernelDictionary;

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

        $dir = $this->kernel->getRootDir() . '/cache';
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
        $script = "(function () { $('a:contains(\"{$label}\")')[0].click(); })();";
        $this->getSession()->evaluateScript($script);
    }

    /**
     * @Given /^I check the "([^"]*)" line$/
     */
    public function iCheckTheLine($text)
    {
        $script = "(function () {
            var row = $('tr:contains(\"{$text}\")');
            var selector = '#' + row.attr('id') + ' input:checkbox';
            var checkbox = $(selector);
            $(checkbox).prop('checked', 'checked');
        })();";
        $this->getSession()->evaluateScript($script);
    }

    /**
     * Connect the user with login and password
     *
     * @Given /^I\'m connected with login "([^"]*)" and password "([^"]*)"$/
     */
    public function iMConnectedWithLoginAndPassword($login, $password)
    {
        $login    = $this->fixStepArgument($login);
        $password = $this->fixStepArgument($password);
        return array(
            new Step\When('I am on "/login"'),
            new Step\When('I fill in "Nom d\'utilisateur ou email" with "'. $login . '"'),
            new Step\When('I fill in "Mot de passe (Mot de passe oublié ?)" with "'. $password . '"'),
            new Step\When('I press "Connexion"')
        );
    }

    /**
     * Clicks element with specified css.
     *
     * @When /^(?:|I )click on "(?P<element>(?:[^"]|\\")*)"$/
     */
    public function iClickOn($locator)
    {
        $locator = $this->fixStepArgument($locator);
        $element = $this->getSession()->getPage()->find('css', $locator);

        if (null === $element) {
            throw new ElementNotFoundException($this->getSession(), 'element', 'css', $locator);
        }

        $element->click();
    }

    /**
     * @Given /^I click on the (\d+)(st|nd|rd|th) "([^"]*)"$/
     */
    public function iClickOnTheNth($index, $position, $locator)
    {
        $locator  = $this->fixStepArgument($locator);
        $elements = $this->getSession()->getPage()->findAll('css', $locator);

        if (0 === count($elements)) {
            throw new ElementNotFoundException($this->getSession(), 'elements', 'css', $locator);
        }

        if (!isset($elements[$index - 1])) {
            throw new ExpectationException(sprintf("The %s%s '%s' element was not found in the page.", $index, $position, $locator), $this->getSession());
        }

        $elements[$index - 1]->click();
    }

    /**
     * Fills in tinymce field with specified id
     *
     * @Given /^I fill in tinymce "([^"]*)" with "([^"]*)"$/
     */
    public function iFillInTinymceWith($locator, $value)
    {
        $locator = $this->fixStepArgument($locator) . '_ifr';
        $value   = $this->fixStepArgument($value);

        // Just checking if the iframe exists
        $this->getSession()->switchToIFrame($locator);
        $this->getSession()->switchToIFrame(null);

        $script = <<<EOL
var iframe = document.getElementById('$locator');
iframe.contentWindow.document.body.innerHTML = "$value";
EOL;
        $this->getSession()->executeScript($script);
    }

    /**
     * @Given /^I fill in "([^"]*)" with "([^"]*)" for autocomplete$/
     */
    public function iFillInWithForAutocomplete($locator, $value)
    {
        $field = $this->getSession()->getPage()->findField($locator);

        $field->focus();

        $this->fillField($locator, $value);
    }

    /**
     * @Given /^I wait for the suggestion box to appear$/
     */
    public function iWaitForTheSuggestionBoxToAppear()
    {
        $this->getSession()->wait(5000,
            "$('.ui-autocomplete').children().length > 0"
        );
    }

    /**
     * @Given /^I wait for the popup to appear$/
     */
    public function iWaitForThePopupToAppear()
    {
        $this->getSession()->wait(5000,
            "$('#modal-form').css('display') == 'block'"
        );
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

    /**
     * @Then /^test response status code for this url:$/
     */
    public function testResponseStatusCodeForThisUrl(TableNode $table)
    {
        $steps = array();
        $hash  = $table->getHash();

        foreach ($hash as $row) {
            $steps[] = new Step\When('I am on "' . $row['url'] . '"');
            $steps[] = new Step\When('the response status code should be ' . $row['code']);
        }

        return $steps;
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

    /**
     * @Given /^I wait "([^"]*)" seconds$/
     */
    public function waitSeconds($seconds)
    {
        $this->getSession()->wait(1000*$seconds);
    }

    private function getBaseUrl()
    {
        return str_replace('app.php/', '', $this->getMinkParameter('base_url'));
    }
}
