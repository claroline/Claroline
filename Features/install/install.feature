Feature: Install
    In order to install the platform
    as a user
    I need to do

    Background:
        Given the database is empty
        And operation.xml is initialized
        And base url is web
        And installation directories are writable

    Scenario: Successfully install
        Given I am on "/install.php"
        When I follow "Next"
        And I follow "Next"
        And I fill in "password" with "root"
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
        And I press "do-install"
        Then database should exists
        And user "root" should exists
