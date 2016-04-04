#Feature: Workspace list
#  Scenario: The administrator can see every non personal workspaces
#    Given the user "user" is created
#    And the workspace "workspace_1" is created by "user"
#    When I am on "/workspaces/"
#    Then the response should contain "workspace_1"
