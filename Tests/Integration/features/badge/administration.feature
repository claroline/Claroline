Feature:
    Administration badge pages

    Scenario: Successful access
        Given I'm connected with login "JohnDoe" and password "JohnDoe"
        Then test response status code for this url:
            | url               | code |
            | /admin/badges     | 200  |
            | /admin/badges/add | 200  |

    @javascript
    Scenario: Successful creation of a badge, followed by its deletion
        Given I'm connected with login "JohnDoe" and password "JohnDoe"
        And I go to "/admin/badges"
        Then I should see "Gestion des badges"
        And I should see 0 "#badges .badge" elements
        And I follow "Ajouter un badge"
        Then I should be on "/admin/badges/add"
        And I attach the file "/home/maxime/Images/html5_badge_256.png" to "badge_form_file"
        And I fill in "badge_form_frTranslation_name" with "Badge de test"
        And I fill in "badge_form_frTranslation_description" with "C'est un badge de test"
        And I fill in tinymce "badge_form_frTranslation_criteria" with "Pour avoir ce badge de test il faut se le voir attribuer."
        And I press "Ajouter"
        Then I should be on "/admin/badges"
        And I should see "Badge ajouté avec succès."
        And I should see 1 "#badges .badge" elements
        Then I click on "#badges .badge .badge_menu_link"
        And I follow "Supprimer"
        And I should see "Suppression d'un badge"
        And I should see "Etes-vous sûr de vouloir supprimer le badge Badge de test ?"
        Then I press "Supprimer"
        Then I should be on "/admin/badges"
        And I should see 0 "#badges .badge" elements
        And I should see "Badge supprimé avec succès."