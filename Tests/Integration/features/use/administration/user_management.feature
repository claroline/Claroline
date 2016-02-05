Feature: User management

  Scenario: Create a user
    Given the admin account "root" is created
      And I log in with "root"/"root"
      And I am on "/admin/user/form"
    When I fill in the following:
      | First name          | Bob           |
      | Last name           | Doe           |
      | Username            | bdoe          |
      | Password            | secret_pwd    |
      | Verification        | secret_pwd    |
      | Mail                | bdoe@mail.com |
      | Administrative code | some_code     |
      And I press "Ok"
    Then I should see "root"
      And I should see "bdoe"

  Scenario: Import users from csv
    Given the admin account "root" is created
      And I log in with "root"/"root"
      And I am on "/admin/user/management/import/form"
    When I attach the file "claroline/core-bundle/Tests/Stub/users.txt" to "File"
      And I press "Ok"
    Then I should be on "/admin/users/page/1/max/50/order"
      And I should see 10 ".row-user" elements

  @javascript
  Scenario: Edit a user
      Given the following accounts are created:
        | root  | ROLE_ADMIN  |
        | jane  | ROLE_USER   |
        And I log in with "root"/"root"
    When I am on "/admin/users/page/1/max/50/order"
      And I follow "jane"
      And I follow the hidden "Edit"
      And I fill in "Username" with "Jeanne"
      And I press "Ok"
    Then I should be on "/admin/users/page/1/max/50/order"
      And I should see "Jeanne"

  @javascript
  Scenario: Delete a user
    Given the following accounts are created:
      | root  | ROLE_ADMIN  |
      | jane  | ROLE_USER   |
      And I log in with "root"/"root"
      And I am on "/admin/users/page/1/max/50/order"
    When I check the line containing "jane"
      And I press "Delete"
      And I press "Ok"
    Then I should see "root"
      And I should not see "jane"

  @javascript
  Scenario: Search users
    Given the following accounts are created:
      | root  | ROLE_ADMIN       |
      | jack  | ROLE_WS_CREATOR  |
      | jane  | ROLE_USER        |
      | bill  | ROLE_USER        |
      And I log in with "root"/"root"
      And I am on "/admin/users/page/1/max/50/order"
    When I fill in "search-items-txt" with "ja"
      And I press "search-button"
    Then I should see "jack"
      And I should see "jane"
