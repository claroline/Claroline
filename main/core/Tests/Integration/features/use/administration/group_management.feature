Feature: Group management

  Scenario: Create a group
    Given the admin account "root" is created
      And I log in with "root"/"root"
      And I am on "/admin/group/form"
    When I fill in "Name" with "Group 1"
      And I press "Ok"
    Then I should be on "/admin/groups/page/1/max/50/order"
      And I should see "Group 1"

  @javascript
  Scenario: Delete a group
    Given the admin account "root" is created
    And the following groups are created:
      | Bachelor 1  | ROLE_USER  |
      | Bachelor 2  | ROLE_USER  |
      | Master 1    | ROLE_USER  |
    And I log in with "root"/"root"
    And I am on "/admin/groups/page/1/max/50/order"
    When I check the line containing "Bachelor 1"
    And I press "Delete"
    And I press "Ok"
    Then I should see "Master 1"
    And I should not see "Bachelor 1"

  @javascript
  Scenario: Search groups
    Given the admin account "root" is created
      And the following groups are created:
        | Admins    | ROLE_ADMIN |
        | Bachelor  | ROLE_USER  |
        | Master 1  | ROLE_USER  |
        | Master 2  | ROLE_USER  |
      And I log in with "root"/"root"
      And I am on "/admin/groups/page/1/max/50/order"
    When I fill in "search-items-txt" with "master"
      And I press "search-button"
    Then I should see 2 ".row-group" element

#  Scenario: Edit a group
#    Given the admin account "root" is created
#      And the following groups are created:
#        | Bachelor  | ROLE_USER |
#    When I am on "/admin/groups/page/1/max/50/order"
#      And I follow "Settings"
#      And I fill in "Name" with "Master"
#      And I press "Ok"
#    Then I should be on "/admin/groups/page/1/max/50/order"
#      And I should see "Master"
#      And I should not see "Bachelor"
#
#  Scenario: add a user to a group, search that user in the group and removes it.
#    Given the group "group" is created
#    When I am on "/admin/groups/page/1/max/50/order"
#    And I follow "group"
#    And I follow "Add user"
#    And I check the "root" line
#    And I press "Add"
#    And I press "Ok"
#    And I go to "/admin/groups/page/1/max/50/order"
#    And I follow "group"
#    Then I should see "root" in the ".row-user" element
#    When I fill in "search-items-txt" with "ro"
#    And I press "search-button"
#    Then I should see "root" in the ".row-user" element
#    When I move backward one page
#    And I check the "root" line
#    And I press "Delete"
#    And I press "Ok"
#    And I go to "/admin/groups/page/1/max/50/order"
#    And I follow "group"
#    Then I should see 1 "tr" elements
