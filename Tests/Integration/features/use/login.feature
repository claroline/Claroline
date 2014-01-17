Feature:
    Test login page

    Scenario: Successful login
        Given I'm connected with login "JohnDoe" and password "JohnDoe"
        Then I should be on the homepage
        And I should see "John Doe"
        And I should see "Administration"

    Scenario: UnSuccessful login
        Given I'm connected with login "JohnDoe" and password "httjhfghx"
        Then I should be on "/login"
        And I should see "La connexion a échoué. Vérifiez que votre nom d'utilisateur et votre mot de passe sont corrects."