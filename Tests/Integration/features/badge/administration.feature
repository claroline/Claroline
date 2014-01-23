Feature:
    Administration badge pages

    Scenario: Successful access
        Given I'm connected with login "JohnDoe" and password "JohnDoe"
        Then test response status code for this url:
            | url               | code |
            | /admin/badges     | 200  |
            | /admin/badges/add | 200  |

    @javascript
    Scenario: Successful creation of a badge without rules
        Given I'm connected with login "JohnDoe" and password "JohnDoe"
        And I go to "/admin/badges"
        Then I should see "Gestion des badges"
        And I should see 0 "#badges .badge" elements
        And I follow "Ajouter un badge"
        Then I should be on "/admin/badges/add"
        And I attach the file "vendor/claroline/core-bundle/Claroline/CoreBundle/Resources/public/images/test/html5_logo.png" to "badge_form_file"
        And I fill in "badge_form_frTranslation_name" with "Badge de test"
        And I fill in "badge_form_frTranslation_description" with "C'est un badge de test"
        And I fill in tinymce "badge_form_frTranslation_criteria" with "Pour avoir ce badge de test il faut se le voir attribuer."
        And I press "Ajouter"
        Then I should be on "/admin/badges"
        And I should see "Badge ajouté avec succès."
        And I should see 1 "#badges .badge" elements
        Then I click on "#badges .badge .badge_menu_link"
        And I follow "Supprimer"
        And I wait "1" seconds
        And I should see "Suppression d'un badge"
        And I should see "Etes-vous sûr de vouloir supprimer le badge Badge de test ?"
        Then I press "Supprimer"
        Then I should be on "/admin/badges"
        And I should see 0 "#badges .badge" elements
        And I should see "Badge supprimé avec succès."

    @javascript
    Scenario: Dealing with errors when attempting to create badge
        Given I'm connected with login "JohnDoe" and password "JohnDoe"
        And I go to "/admin/badges"
        Then I should see "Gestion des badges"
        And I should see 0 "#badges .badge" elements
        And I follow "Ajouter un badge"
        Then I should be on "/admin/badges/add"
        And I press "Ajouter"
        Then I should be on "/admin/badges/add"
        And I should see "Au moins une traduction complète doit être fournie pour le badge."
        And I should see "Une image doit être définie sur le badge"
        And I fill in "badge_form_frTranslation_name" with "Badge de test"
        And I press "Ajouter"
        Then I should be on "/admin/badges/add"
        And I should see "Au moins une traduction complète doit être fournie pour le badge."
        And I should see "Une image doit être définie sur le badge"
        And I fill in "badge_form_frTranslation_description" with "C'est un badge de test"
        And I press "Ajouter"
        Then I should be on "/admin/badges/add"
        And I should see "Au moins une traduction complète doit être fournie pour le badge."
        And I should see "Une image doit être définie sur le badge"
        And I fill in tinymce "badge_form_frTranslation_criteria" with "Pour avoir ce badge de test il faut se le voir attribuer."
        And I press "Ajouter"
        Then I should be on "/admin/badges/add"
        And I should see "Une image doit être définie sur le badge"
        And I attach the file "vendor/claroline/core-bundle/Claroline/CoreBundle/Resources/public/images/test/html5_logo.png" to "badge_form_file"
        And I press "Ajouter"
        Then I should be on "/admin/badges"
        And I should see "Badge ajouté avec succès."
        And I should see 1 "#badges .badge" elements
        Then I click on "#badges .badge .badge_menu_link"
        And I follow "Supprimer"
        And I wait "1" seconds
        And I should see "Suppression d'un badge"
        And I should see "Etes-vous sûr de vouloir supprimer le badge Badge de test ?"
        Then I press "Supprimer"
        Then I should be on "/admin/badges"
        And I should see 0 "#badges .badge" elements
        And I should see "Badge supprimé avec succès."

    @javascript
    Scenario: Successful creation of a badge with rules
        Given I'm connected with login "JohnDoe" and password "JohnDoe"
        And I go to "/admin/badges"
        Then I should see "Gestion des badges"
        And I should see 0 "#badges .badge" elements
        And I follow "Ajouter un badge"
        Then I should be on "/admin/badges/add"
        And I click on "#add_rule"
        Then I should see 1 "#ruleTabs li[id^=tabrule]" elements
        And I should see "Règle 1"
        And I should see "Détails de la règle"
        And I select "Ressource" from "badge_form_rules_0_action_"
        And I select "Blog" from "badge_form_rules_0_action__"
        And I select "Création d'un article dans un blog" from "badge_form_rules_0_action___"
        And I attach the file "vendor/claroline/core-bundle/Claroline/CoreBundle/Resources/public/images/test/html5_logo.png" to "badge_form_file"
        And I fill in "badge_form_frTranslation_name" with "Badge de test"
        And I fill in "badge_form_frTranslation_description" with "C'est un badge de test"
        And I fill in tinymce "badge_form_frTranslation_criteria" with "Pour avoir ce badge de test il faut se le voir attribuer."
        And I press "Ajouter"
        Then I should be on "/admin/badges"
        And I should see "Badge ajouté avec succès."
        And I should see 1 "#badges .badge" elements
        And I follow "Badge de test"
        Then I should see "Badge 'Badge de test'"
        And I should see "Badge attribué à :"
        And I follow "Modifier"
        Then I should see "Modification du badge 'Badge de test'"
        And I should see "Traduction française"
        Then I should see 1 "#ruleTabs li[id^=tabrule]" elements
        And I should see "Règle 1"
        And I should see "Détails de la règle"
        And the "badge_form_frTranslation_name" field should contain "Badge de test"
        And the "badge_form_frTranslation_description" field should contain "C'est un badge de test"
        And the "badge_form_rules_0_action_" field should contain "Ressource"
        And the "badge_form_rules_0_action__" field should contain "Blog"
        And the "badge_form_rules_0_action___" field should contain "Création d'un article dans un blog"
        And I follow "Supprimer"
        Then I wait "1" seconds
        And I should see "Suppression d'un badge"
        And I should see "Etes-vous sûr de vouloir supprimer le badge Badge de test ?"
        Then I press "Supprimer"
        Then I should be on "/admin/badges"
        And I should see 0 "#badges .badge" elements
        And I should see "Badge supprimé avec succès."

    @javascript
    Scenario: Successful creation of a badge with rule on badge awarding
        Given I'm connected with login "JohnDoe" and password "JohnDoe"
        And I go to "/admin/badges"
        Then I should see "Gestion des badges"
        And I should see 0 "#badges .badge" elements
        And I follow "Ajouter un badge"
        Then I should be on "/admin/badges/add"
        And I click on "#add_rule"
        Then I should see 1 "#ruleTabs li[id^=tabrule]" elements
        And I should see "Règle 1"
        And I should see "Détails de la règle"
        And I select "Badge" from "badge_form_rules_0_action_"
        Then I should see "Lorsque l'action est l'attribution d'un badge"
        And I select "Attribution d'un badge" from "badge_form_rules_0_action__"
        And I click on "#s2id_badge_form_rules_0_badge a"
        Then I should see "Aucun résultat trouvé"
        And I attach the file "vendor/claroline/core-bundle/Claroline/CoreBundle/Resources/public/images/test/html5_logo.png" to "badge_form_file"
        And I fill in "badge_form_frTranslation_name" with "Badge de test"
        And I fill in "badge_form_frTranslation_description" with "C'est un badge de test"
        And I fill in tinymce "badge_form_frTranslation_criteria" with "Pour avoir ce badge de test il faut se le voir attribuer."
        And I press "Ajouter"
        Then I should be on "/admin/badges"
        And I should see "Badge ajouté avec succès."
        And I should see 1 "#badges .badge" elements
        And I click on "#badges .badge .badge_menu_link"
        Then I follow "Supprimer"
        And I wait "1" seconds
        And I should see "Suppression d'un badge"
        And I should see "Etes-vous sûr de vouloir supprimer le badge Badge de test ?"
        Then I press "Supprimer"
        Then I should be on "/admin/badges"
        And I should see 0 "#badges .badge" elements
        And I should see "Badge supprimé avec succès."

    @javascript @current
    Scenario: Creation of a badge awarding demand with validation of the awarding
        Given I'm connected with login "JohnDoe" and password "JohnDoe"
        And I go to "/admin/badges"
        Then I should see "Gestion des badges"
        And I should see 0 "#badges .badge" elements
        And I follow "Ajouter un badge"
        Then I should be on "/admin/badges/add"
        And I attach the file "vendor/claroline/core-bundle/Claroline/CoreBundle/Resources/public/images/test/html5_logo.png" to "badge_form_file"
        And I fill in "badge_form_frTranslation_name" with "Badge de test"
        And I fill in "badge_form_frTranslation_description" with "C'est un badge de test"
        And I fill in tinymce "badge_form_frTranslation_criteria" with "Pour avoir ce badge de test il faut se le voir attribuer."
        And I press "Ajouter"
        Then I should be on "/admin/badges"
        And I should see "Badge ajouté avec succès."
        And I should see 1 "#badges .badge" elements
        And I click on the 2nd ".navbar-collapse .navbar-right a.dropdown-toggle"
        Then I should see "Mes badges"
        And I follow "Mes badges"
        Then I should be on "/profile/badge"
        And I should see "Aucun badge."
        And I follow "Réclamer un badge"
        Then I should be on "/profile/badge/claim"
        And I should see "Formulaire de demande d'attribution de badge"
        And I fill in "badge_claim_form[badge]" with "Badge" for autocomplete
        And I wait for the suggestion box to appear
        Then I should see "Badge de test"
        And I click on the 1st ".ui-autocomplete li"
        And I press "Réclamer"
        Then I should see "Demande d'attribution de badge effectué avec succès."
        And I should see "1 demande d'attribution de badge en cours"
        And I follow "1 demande d'attribution de badge en cours"
        Then I should see "Badge de test"
        And I go to "/admin/badges"
        And I click on the 2nd "#myTab li a"
        And I should see "Badge de test"
        And I follow "Valider l'attribution du badge"
        And I wait "1" seconds
        And I should see "Validation de l'attribution d'un badge"
        And I should see "Etes-vous sûr de vouloir attribuer le badge Badge de test à l'utilisateur John Doe ?"
        Then I press "Attribuer"
        Then I should be on "/admin/badges"
        And I should see "Validation de l'attribution du badge effectuée avec succès."
        And I click on the 2nd "#myTab li a"
        And I should see "Aucune demande d'attribution."
        And I go to "/profile/badge"
        Then I should see 1 ".badge_list li.node" elements
        And I should see "Badge de test"
        Then I go to "/admin/badges"
        And I follow "Badge de test"
        And I should see 1 "#award_users_container table tbody tr" elements
        And I should not see "Badge attribué à aucun utilisateur."
        And I follow "Désattribution d'un badge"
        And I wait "1" seconds
        And I should see "Désattribution d'un badge"
        And I should see "Etes-vous sûr de vouloir désattribuer le badge Badge de test à John Doe?"
        Then I press "Désattribuer"
        And I should see "Désattribution du badge effectuée avec succès."
        And I should see "Badge attribué à aucun utilisateur."
        And I go to "/profile/badge"
        Then I should see 0 ".badge_list li.node" elements
        And I should see "Aucun badge."
        And I go to "/admin/badges"
        Then I click on "#badges .badge .badge_menu_link"
        And I follow "Supprimer"
        And I wait "1" seconds
        And I should see "Suppression d'un badge"
        And I should see "Etes-vous sûr de vouloir supprimer le badge Badge de test ?"
        Then I press "Supprimer"
        Then I should be on "/admin/badges"
        And I should see 0 "#badges .badge" elements
        And I should see "Badge supprimé avec succès."
