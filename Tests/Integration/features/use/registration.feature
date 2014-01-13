@registration
Feature: Registration
  Background:
    Given the cache directory is writable

    Scenario: Successfully registrate
      Given self registration is allowed
      And the platform is initialized
      When I am on "/register/form"
      And I fill in "Last name" with "lastName"
      And I fill in "First name" with "firstName"
      And I fill in "Username" with "username"
      And I fill in "Password" with "password"
      And I fill in "Verification" with "password"
      And I fill in "Mail" with "mail@clar.oline"
      And I select "en" from "Language"
      And I press "Ok"
      And I am on "/login"
      And I fill in "username" with "username"
      And I fill in "password" with "password"
      And I press "Login"
      Then I should be on "/"

    Scenario: Fail to registrate
      Given self registration is allowed
      When I am on "/register/form"
      And I fill in "Last name" with "lastName"
      And I fill in "First name" with "firstName"
      And I fill in "Username" with "é#fdsq585"
      And I fill in "Password" with " "
      And I fill in "Verification" with " "
      And I fill in "Mail" with "mail@clar.oline"
      And I select "en" from "Language"
      And I press "Ok"
      Then the response should contain "Special characters are not allowed"
      Then the response should contain "This value is too short. It should have 4 characters or more."
      And the response should contain "This value is too short"

    Scenario: Try to access the registration form when it's disabled
      Given self registration is disabled
      When I am on "/register/form"
      Then I should be on "/login"

