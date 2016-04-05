Feature:
    My portfolios page

    Scenario: Successful access
        Given the admin account "JohnDoe" is created
        When I log in with "JohnDoe"/"JohnDoe"
        And I go to "/portfolio"
        Then the response status code should be 200
        And I should see "My portfolios"
        When I go to "/portfolio/add"
        Then the response status code should be 200
        And I should see "Adding a portfolio"