Feature: Login page

  Scenario: Successful login
    Given the admin account "root" is created
    When I log in with "root"/"root"
    Then I should be on the homepage
      And I should see "root root"
      And I should see "Administration"

  Scenario: Unsuccessful login
    Given I log in with "unknown_user"/"wrong_password"
    Then I should be on "/login"
      And I should see "Login has failed"
