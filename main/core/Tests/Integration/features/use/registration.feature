Feature: Registration

  Scenario: Successfully register
    Given self registration is allowed
    When I am on "/register/form"
      And I fill in the following:
        | First name    | Bob           |
        | Last name     | Doe           |
        | Username      | bdoe          |
        | Password      | secret_pwd    |
        | Verification  | secret_pwd    |
        | Mail          | bdoe@mail.com |
      And I select "en" from "Language"
      And I press "Ok"
      And I log in with "bdoe"/"secret_pwd"
    Then I should be on "/"
      And I should see "Desktop"

  Scenario: Fail to register
    Given self registration is allowed
    When I am on "/register/form"
      And I fill in the following:
        | First name    | Bob           |
        | Last name     | Doe           |
        | Username      | é#fdsq585     |
        | Password      | a             |
        | Verification  | a             |
        | Mail          | bdoe@mail.com |
      And I press "Ok"
    Then I should see "Special characters are not allowed"
      And I should see "This value is too short. It should have 4 characters or more."

  Scenario: Try to access the registration form when it's disabled
    Given self registration is disabled
    When I am on "/register/form"
    Then the response status code should be 403
