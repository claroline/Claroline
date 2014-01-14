@install
Feature: Install
    In order to install the platform
    as a user
    I need to use the installer

    Background:
        Given the database does not exists
        And operation.xml is initialized
        And base url is web
        And installation directories are writable

    Scenario: Successfully install
        Given I am on "/install.php"
        When I follow "Next"
        And I follow "Next"
        And I fill in "name" with database name
        And I fill in "user" with database username
        And I fill in "password" with database password
        And I press "Next"
        And I fill in "supportEmail" with "mail@support.com"
        And I press "Next"
        And I fill in "firstName" with "root"
        And I fill in "lastName" with "root"
        And I fill in "username" with "root"
        And I fill in "password" with "root"
        And I fill in "email" with "root@root.net"
        And I press "Next"
        And I follow "Skip"
        And I press "Launch installation now"
        And show last response
        Then database should exists
        And user "root" should exists
        And I should see "Claroline"
