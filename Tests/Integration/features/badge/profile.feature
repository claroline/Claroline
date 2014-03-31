Feature:
    Profile badge pages

    Scenario: Successful access
        Given the admin account "JohnDoe" is created
        When I log in with "JohnDoe"/"JohnDoe"
        Then test response status code for this url:
            | url                  | code |
            | /profile/badge/      | 200  |
            | /profile/badge/claim | 200  |