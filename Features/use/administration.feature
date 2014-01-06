@administration
Feature: Administration

    Background:
        Given the cache directory is writable
        And the database is initialized
        And the user "root" is created
        And I am on "/login"
        And I fill in "username" with "root"
        And I fill in "password" with "root"
        And I press "Login"

    Scenario: Successfully create a user
        Given I am on "/admin/user/form"
        When I fill in "First name" with "firstname"
        And I fill in "Last name" with "lastName"
        And I fill in "Username" with "username"
        And I fill in "Password" with "password"
        And I fill in "Verification" with "password"
        And I fill in "Administrative code" with "code"
        And I fill in "Mail" with "mail@clar.oline"
        And I press "Ok"
        Then the platform should have "2" "User"

    Scenario: Fail to create a user
        Given I am on "/admin/user/form"
        When I fill in "First name" with "firstname"
        And I fill in "Last name" with "lastName"
        And I fill in "Username" with "username"
        And I fill in "Password" with "password"
        And I fill in "Verification" with "password"
        And I fill in "Administrative code" with "code"
        And I fill in "Mail" with "mail@clar.oline"
        And I press "Ok"



   # Scenario: Sucessfully create users from csv
   # Scenario: Fails to create users from csv
   # Scenario: Sucessfully create group
   # Scenario: Fail to create group
   # Scenario: Fail to edit group settings
   # Scenario: Sucessfully edit group settings
   # Scenario: Sucessfully edit user properties
   # Scenario: Fail to edit user properties
   # Scenario: Display the plateform users
   # Scenario: Search the plateform users
   # Scenario: Display the plateform groups
   # Scenario: Search the plateform groups
   # Scenario: Display the group's users
   # Scenario: Search the group's users
   # Scenario: Sucessfully edit the platform options
   # Scenario: Sucessfully delete group
   # Scenario: delete users



