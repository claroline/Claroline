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
use Behat\Behat\Event\ScenarioEvent;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Exception\ExpectationException;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Behat\MinkExtension\Context\MinkContext;
use Claroline\CoreBundle\Library\Installation\Settings\SettingChecker;
use Goutte\Client;

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

    /**
     * @BeforeScenario
     */
    public function beforeScenario(ScenarioEvent $event)
    {
        if (in_array('javascript', $event->getScenario()->getTags())) {
            $client = new Client();
            $client->request('GET', $this->getUrl('test/reinstall'));
            $status = $client->getResponse()->getStatus();
            $content = $client->getResponse()->getContent();
        } else {
            $this->visit('test/reinstall');
            $status  = $this->getSession()->getStatusCode();
            $content = $this->getSession()->getPage()->getContent();
        }

        $this->checkForResponseError($status, $content, 'Unable to init platform');
    }

    /**
     * @Given /^the admin account "([^"]*)" is created$/
     */
    public function theAdminAccountIsCreated($username)
    {
        $this->loadFixture(
            'Claroline\CoreBundle\DataFixtures\Test\LoadUserData',
            array(array('username' => $username, 'role' => 'ROLE_ADMIN'))
        );
    }

    /**
     * @Given /^the following accounts are created:$/
     */
    public function theFollowingAccountsAreCreated(TableNode $table)
    {
        $users = array();

        foreach ($table->getRows() as $row) {
            $users[] = array('username' => $row[0], 'role' => $row[1]);
        }

        $this->loadFixture('Claroline\CoreBundle\DataFixtures\Test\LoadUserData', $users);
    }

    /**
     * @Given /^the following groups are created:$/
     */
    public function theFollowingGroupsAreCreated(TableNode $table)
    {
        $groups = array();

        foreach ($table->getRows() as $row) {
            $groups[] = array('name' => $row[0], 'role' => $row[1]);
        }

        $this->loadFixture('Claroline\CoreBundle\DataFixtures\Test\LoadGroupData', $groups);
    }

    /**
     * Connects the user with login and password
     *
     * @Given /^I log in with "([^"]*)"\/"([^"]*)"$/
     */
    public function iLogInWith($login, $password)
    {
        return array(
            new Step\When('I am on "/login"'),
            new Step\When('I fill in "Username or email" with "'. $login . '"'),
            new Step\When('I fill in "Password" with "'. $password . '"'),
            new Step\When('I press "Login"')
        );
    }

    /**
     * @When /^I follow the hidden "([^"]*)"$/
     */
    public function iFollowTheHidden($label)
    {
        $script = "(function () { $('a:contains(\"{$label}\")')[0].click(); })();";
        $this->getSession()->evaluateScript($script);
    }

    /**
     * @Given /^I check the line containing "([^"]*)"$/
     */
    public function iCheckTableLineContaining($text)
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

    protected function loadFixture($fixtureFqcn, array $args = array())
    {
        $client = new Client();
        $client->request(
            'POST',
            $this->getUrl('test/fixture/load'),
            array('fqcn' => $fixtureFqcn, 'args' => $args)
        );
        $this->checkForResponseError(
            $client->getResponse()->getStatus(),
            $client->getResponse()->getContent(),
            "Unable to load {$fixtureFqcn} fixture"
        );
    }

    private function getUrl($path)
    {
        return $this->getMinkParameter('base_url') . '/' . $path;
    }

    private function checkForResponseError($status, $content, $exceptionMsg)
    {
        if (preg_match('#<title>([^<]+)#', $content, $matches)) {
            $content = $matches[1];
        }

        if ($status !== 200 || preg_match('/Fatal error/i', $content)) {
            throw new \Exception(
                "{$exceptionMsg}.\n"
                . "Response status is: {$status}\n"
                . "Response content is: {$content}"
            );
        }
    }
}
