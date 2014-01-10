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

    Scenario: Successfully create a user, search it, then remove it
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
        When I am on "/admin/users/page/1/max/50/order"
        And I fill in "search-items-txt" with "us"
        And I press "search-button"
        Then I should see 2 "tr" elements
        When I check the "user" line
        And I press "Delete"
        And I wait "0.1" seconds
        And I press "Ok"
        And I wait "0.3" seconds
        And I go to "/admin/users/page/1/max/50/order"
        Then I should see 2 "tr" elements

    Scenario: Fail to create a user
        Given I am on "/admin/user/form"
        When I fill in "First name" with "firstname"
        And I fill in "Last name" with "lastName"
        And I fill in "Username" with "er*/me"
        And I fill in "Password" with " "
        And I fill in "Verification" with " "
        And I fill in "Administrative code" with "code"
        And I fill in "Mail" with "maill@ine.cfds"
        And I press "Ok"
        Then the platform should have "1" "User"
        And I should see "Special characters are not allowed" in the ".help-block" element
        And the response should contain "This value is too short. It should have 4 characters or more."

    Scenario: Successfully create users from csv
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

    Scenario: Successfully create group, search it and then removes it
        Given I am on "/admin/group/form"
        When I fill in "Name" with "group"
        And I press "Ok"
        Then the platform should have "1" "Group"
        When I fill in "search-items-txt" with "ro"
        And I press "search-button"
        Then I should see "group" in the ".row-group" element
        And I check the "group" line
        And I press "Delete"
        And I press "Ok"
        And I go to "/admin/groups/page/1/max/50/order"
        Then the platform should have "0" "Group"

    Scenario: Fail to create group
        Given I am on "/admin/group/form"
        When I fill in "Name" with " "
        And I press "Ok"
        Then the platform should have "0" "Group"

    Scenario: Successfully edit user settings through the user list
        Given the user "user" is created
        When I am on "/admin/users/page/1/max/50/order"
        And I follow "user"
        And I follow the hidden "Edit"
        And I fill in "Username" with "modifiedname"
        And I press "Ok"
        Then the response should contain "modifiedname"
        And I should be on "/admin/users/page/1/max/50/order"

    Scenario: Fail to edit user settings
       Given the user "user" is created
       When I am on "/admin/users/page/1/max/50/order"
       And I follow "user"
       And I follow the hidden "Edit"
       And I fill in "Username" with "'ézvfds"
       And I press "Ok"
       Then I should see "Special characters are not allowed" in the ".help-block" element

    Scenario: The administrator can see every non personal workspaces
        Given the user "user" is created
        And the workspace "workspace_1" is created by "user"
        When I am on "/workspaces/"
        Then the response should contain "workspace_1"

    Scenario: Successfully edit group settings
        Given the group "group" is created
        When I am on "/admin/groups/page/1/max/50/order"
        And I follow "Settings"
        And I fill in "Name" with "newname"
        And I press "Ok"
        Then the response should contain "newname"

     Scenario: Fail to edit group settings
         Given the group "group" is created
         When I am on "/admin/groups/page/1/max/50/order"
         And I follow "Settings"
         And I fill in "Name" with " "
         And I press "Ok"
         Then the response should contain "This value should not be blank."

    Scenario: add a user to a group, search that user in the group and removes it.
        Given the group "group" is created
        When I am on "/admin/groups/page/1/max/50/order"
        And I follow "group"
        And I follow "Add user"
        And I check the "root" line
        And I press "Add"
        And I press "Ok"
        And I go to "/admin/groups/page/1/max/50/order"
        And I follow "group"
        Then I should see "root" in the ".row-user" element
        When I fill in "search-items-txt" with "ro"
        And I press "search-button"
        Then I should see "root" in the ".row-user" element
        When I move backward one page
        And I check the "root" line
        And I press "Delete"
        And I press "Ok"
        And I go to "/admin/groups/page/1/max/50/order"
        And I follow "group"
        Then I should see 1 "tr" elements

  #Scenario: Successfully edit the platform options

  #show last response