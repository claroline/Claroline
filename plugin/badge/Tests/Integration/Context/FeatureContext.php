<?php

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
       * After each scenario, we close the browser
       *
       * @AfterScenario
       */
    public function closeBrowser()
    {
        $this->getSession()->stop();
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
            new Step\When('I press "Login"'),
            new Step\When('I should be on "/desktop/tool/open/home"')
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
     * Step for testing select2 autocomplete field
     *
     * @Given /^I fill in "([^"]*)" with "([^"]*)" for autocomplete$/
     */
    public function iFillInWithForAutocomplete($locator, $value)
    {
        $field = $this->getSession()->getPage()->find('css', $locator);
        $fieldBlock = $field->getParent();
        $fieldLink = $fieldBlock->find('css', ' a');
        $fieldLink->click();

        $field = $this->getSession()->getPage()->find('css', '.select2-input');
        $field->setValue($value);
    }

    /**
     * @Given /^I go to personal workspace of "([^"]*)"$/
     */
    public function iGoToPersonalWorkspaceOf($username)
    {
        /** @var \Claroline\CoreBundle\Repository\UserRepository $userRepository */
        $userRepository = $this->getKernel()->getContainer()->get('doctrine')->getManager()->getRepository('ClarolineCoreBundle:User');
        /** @var \Claroline\CoreBundle\Entity\User $user */
        $user = $userRepository->findOneByUsername($username);

        if (null === $user) {
            throw new \InvalidArgumentException('Unknown username.');
        }

        $userPersonalWorkspace = $user->getPersonalWorkspace();

        if (null === $userPersonalWorkspace) {
            throw new \InvalidArgumentException("User doesn't have a personal workspace.");
        }

        $this->getSession()->visit($this->locatePath(sprintf('/workspaces/%d/open/tool/resource_manager', $userPersonalWorkspace->getId())));
    }

    /**
     * @Given /^resource manager is loaded$/
     */
    public function resourceManagerIsLoaded()
    {
        $this->spin(function($context) {
            /** @var \Claroline\CoreBundle\Tests\Integration\Context\FeatureContext $context */
            $resourceManager = $context->getSession()->getPage()->findById('sortable');
            if (null === $resourceManager) {
                return false;
            }

            return ($resourceManager->isVisible());
        });
    }
    /**
     * @Given /^I wait for the suggestion box to appear$/
     */
    public function iWaitForTheSuggestionBoxToAppear()
    {
        $this->spin(function($context) {
            /** @var \Claroline\CoreBundle\Tests\Integration\Context\FeatureContext $context */
            $suggestionBox = $context->getSession()->getPage()->findById('select2-drop');
            if (null === $suggestionBox) {
                return false;
            }

            return ($suggestionBox->isVisible());
        });
    }

    /**
     * @Then /^I should see "([^"]*)" in the suggestion box$/
     */
    public function iShouldSeeInTheSuggestionBox($value)
    {
        $this->spin(function($context) {
            /** @var \Claroline\CoreBundle\Tests\Integration\Context\FeatureContext $context */
            $suggestionBox = $context->getSession()->getPage()->findById('select2-drop');
            if (null === $suggestionBox) {
                return false;
            }

            $suggestions = $suggestionBox->find('css', '.select2-results li[class!=select2-searching]');
            if (null === $suggestions) {
                return false;
            }

            return ($suggestions->isVisible());
        });
    }

    /**
     * @Given /^I wait for the popup to appear$/
     */
    public function iWaitForThePopupToAppear()
    {
        $this->spin(function($context) {
            /** @var \Claroline\CoreBundle\Tests\Integration\Context\FeatureContext $context */
            $suggestionBox = $context->getSession()->getPage()->findById('modal-form');
            if (null === $suggestionBox) {
                return false;
            }

            return ($suggestionBox->isVisible());
        });
    }

    /**
     * @Given /^I wait for the confirm popup to appear$/
     */
    public function iWaitForTheConfirmPopupToAppear()
    {
        $this->spin(function($context) {
            /** @var \Claroline\CoreBundle\Tests\Integration\Context\FeatureContext $context */
            $suggestionBox = $context->getSession()->getPage()->find('css', '.modal[id^=confirm]');
            if (null === $suggestionBox) {
                return false;
            }

            return ($suggestionBox->isVisible());
        });
    }

    /**
     * @Given /^I click on "([^"]*)" in the resource manager$/
     */
    public function iClickOnInTheResourceManager($locator)
    {
        $this->spin(function($context) use($locator) {
            /** @var \Claroline\CoreBundle\Tests\Integration\Context\FeatureContext $context */
            $element = $context->getSession()->getPage()->find('css', $locator);
            if (null === $element) {
                return false;
            }

            $element->click();
            return true;
        });
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
            $steps[] = new Step\When('I should be on "' . $row['url'] . '"');
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

    public function spin($lambda, $wait = 5)
    {
        for ($i = 0; $i < $wait; $i++)
        {
            try {
                if ($lambda($this)) {
                    return true;
                }
            } catch (\Exception $e) {
                // do nothing
            }

            sleep(1);
        }

        $backtrace = debug_backtrace();

        throw new \Exception("Timeout thrown by " . $backtrace[1]['class'] . "::" . $backtrace[1]['function']);
    }
}
