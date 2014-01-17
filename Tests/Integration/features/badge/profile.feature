Feature:
    Profile badge pages

    Scenario: Successful access
        Given I'm connected with login "JohnDoe" and password "JohnDoe"
        And I am on "/profile/badge"
        Then the response status code should be 200