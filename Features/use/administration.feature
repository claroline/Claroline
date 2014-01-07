@administration
Feature: Administration

    Background:
        Given the cache directory is writable
        And the platform is initialized
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
        And I fill in "Username" with "er*/me"
        And I fill in "Password" with "pd"
        And I fill in "Verification" with "password"
        And I fill in "Administrative code" with "code"
        And I fill in "Mail" with "maill@ine.cfds"
        And I press "Ok"
        Then the platform should have "1" "User"
        And I should see "Special characters are not allowed" in the ".help-block" element

    Scenario: Sucessfully create users from csv
        Given I am on "/admin/user/management/import/form"
        When I attach the file "users.txt" to "File"
        And I press "Ok"
        Then the platform should have "10" "User"

    Scenario: Fails to create users from csv
        Given I am on "/admin/user/management/import/form"
        When I attach the file "users_error.txt" to "File"
        And I press "Ok"
        Then the platform should have "1" "User"
        And the response should contain "Line 1: u1 : This value is too short. It should have 3 characters or more."
        And the response should contain "Line 2: pé' : This value is too short. It should have 4 characters or more."
        And the response should contain "The username usee5 was found at lines: 5, 6 "
        And the response should contain "The email ClaudiaTortelloni@claroline.net was found at lines: 5, 6 "

    Scenario: Sucessfully create group
        Given I am on "/admin/group/form"
        When I fill in "Name" with "name"
        And I press "Ok"
        Then the platform should have "1" "Group"

    Scenario: Fail to create group
        Given I am on "/admin/group/form"
        When I fill in "Name" with " "
        And I press "Ok"
        Then the platform should have "0" "Group"


    #Scenario: Sucessfully edit group settings
    #Scenario: Fail to edit group settings
    #Scenario: Sucessfully edit user properties
    #Scenario: Fail to edit user properties
    #Scenario: Display the plateform users
    #Scenario: Search the plateform users
    #Scenario: Display the plateform groups
    #Scenario: Search the plateform groups
    #Scenario: Display the group's users
    #Scenario: Search the group's users
    #Scenario: Sucessfully edit the platform options
    #Scenario: Sucessfully delete group
    #Scenario: delete users
