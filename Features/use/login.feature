Feature: Login
    In order to login
    as a user
    I need to do

    Background:
        Given the cache directory is writable

    Scenario: Successfully log
        Given I am on "/login"
        When I fill in "username" with "root"
        And I fill in "password" with "root"
        And I press "Login"
        Then I should see "Desktop"
