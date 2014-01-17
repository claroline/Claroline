Feature:
    Profile badge pages

    Scenario: Successful access
        Given I'm connected with login "JohnDoe" and password "JohnDoe"
        Then test response status code for this url:
            | url                  | code |
            | /profile/badge       | 200  |
            | /profile/badge/claim | 200  |