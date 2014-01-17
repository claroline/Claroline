Feature:
    Administration badge pages

    Scenario: Successful access
        Given I'm connected with login "JohnDoe" and password "JohnDoe"
        Then test response status code for this url:
            | url               | code |
            | /admin/badges     | 200  |
            | /admin/badges/add | 200  |