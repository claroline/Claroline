Feature:
    Administration badge pages

    Scenario: Successful access
        Given the admin account "JohnDoe" is created
        When I log in with "JohnDoe"/"JohnDoe"
        Then test response status code for this url:
            | url               | code |
            | /admin/badges     | 200  |
            | /admin/badges/add | 200  |

    @javascript
    Scenario: Successful creation of a badge without rules
        Given the admin account "JohnDoe" is created
        When I log in with "JohnDoe"/"JohnDoe"
        And I go to "/admin/badges"
        Then I should be on "/admin/badges"
        And I should see "Platform badges"
        And I should see 0 ".badge_list .badge" elements
        And I follow "Add a badge"
        Then I should be on "/admin/badges/add"
        And I attach the file "vendor/claroline/core-bundle/Claroline/CoreBundle/Resources/public/images/test/html5_logo.png" to "badge_form_file"
        And I fill in "badge_form_frTranslation_name" with "Badge de test"
        And I fill in "badge_form_frTranslation_description" with "C'est un badge de test"
        And I fill in tinymce "badge_form_frTranslation_criteria" with "Pour avoir ce badge de test il faut se le voir attribuer."
        And I press "Add"
        Then I should be on "/admin/badges"
        And I should see "Badge added with success."
        And I should see 1 ".badge_list .badge" elements
        Then I click on ".badge_list .badge .badge_menu_link"
        And I follow "Delete"
        And I wait for the confirm popup to appear
        And I should see "Deletion of a badge"
        And I should see "Are you sure you want to delete the badge Badge de test ?"
        Then I press "Delete"
        Then I should be on "/admin/badges"
        And I should see 0 ".badge_list .badge" elements
        And I should see "Badge deleted with success."

    @javascript
    Scenario: Dealing with errors when attempting to create badge
        Given the admin account "JohnDoe" is created
        When I log in with "JohnDoe"/"JohnDoe"
        And I go to "/admin/badges"
        Then I should see "Platform badges"
        And I should see 0 ".badge_list .badge" elements
        And I follow "Add a badge"
        Then I should be on "/admin/badges/add"
        And I press "Add"
        Then I should be on "/admin/badges/add"
        And I should see "At least one entire translation must be define on the badge."
        And I should see "An image must be define on the badge"
        And I fill in "badge_form_frTranslation_name" with "Badge de test"
        And I press "Add"
        Then I should be on "/admin/badges/add"
        And I should see "At least one entire translation must be define on the badge."
        And I should see "An image must be define on the badge"
        And I fill in "badge_form_frTranslation_description" with "C'est un badge de test"
        And I press "Add"
        Then I should be on "/admin/badges/add"
        And I should see "At least one entire translation must be define on the badge."
        And I should see "An image must be define on the badge"
        And I fill in tinymce "badge_form_frTranslation_criteria" with "Pour avoir ce badge de test il faut se le voir attribuer."
        And I press "Add"
        Then I should be on "/admin/badges/add"
        And I should see "An image must be define on the badge"
        And I attach the file "vendor/claroline/core-bundle/Claroline/CoreBundle/Resources/public/images/test/html5_logo.png" to "badge_form_file"
        And I press "Add"
        Then I should be on "/admin/badges"
        And I should see "Badge added with success."
        And I should see 1 ".badge_list .badge" elements
        Then I click on ".badge_list .badge .badge_menu_link"
        And I follow "Delete"
        And I wait for the confirm popup to appear
        And I should see "Deletion of a badge"
        And I should see "Are you sure you want to delete the badge Badge de test ?"
        Then I press "Delete"
        Then I should be on "/admin/badges"
        And I should see 0 ".badge_list .badge" elements
        And I should see "Badge deleted with success."

    @javascript
    Scenario: Successful creation of a badge with rules
        Given the admin account "JohnDoe" is created
        When I log in with "JohnDoe"/"JohnDoe"
        And I go to "/admin/badges"
        Then I should see "Platform badges"
        And I should see 0 ".badge_list .badge" elements
        And I follow "Add a badge"
        Then I should be on "/admin/badges/add"
        And I click on "#add_rule"
        Then I should see 1 "#ruleTabs li[id^=tabrule]" elements
        And I should see "Rule 1"
        And I should see "Rule details"
        And I select "Resource" from "badge_form_rules_0_action_"
        And I select "Blog" from "badge_form_rules_0_action__"
        And I select "Post creation in a blog" from "badge_form_rules_0_action___"
        And I attach the file "vendor/claroline/core-bundle/Claroline/CoreBundle/Resources/public/images/test/html5_logo.png" to "badge_form_file"
        And I fill in "badge_form_frTranslation_name" with "Badge de test"
        And I fill in "badge_form_frTranslation_description" with "C'est un badge de test"
        And I fill in tinymce "badge_form_frTranslation_criteria" with "Pour avoir ce badge de test il faut se le voir attribuer."
        And I press "Add"
        Then I should be on "/admin/badges"
        And I should see "Badge added with success."
        And I should see 1 ".badge_list .badge" elements
        And I follow "Badge de test"
        Then I should see "Badge 'Badge de test'"
        And I should see "Start to award this badge."
        And I follow "Edit"
        Then I should see "Edition of the badge 'Badge de test'"
        And I should see "French translation"
        Then I should see 1 "#ruleTabs li[id^=tabrule]" elements
        And I should see "Rule 1"
        And I should see "Rule details"
        And the "badge_form_frTranslation_name" field should contain "Badge de test"
        And the "badge_form_frTranslation_description" field should contain "C'est un badge de test"
        And the "badge_form_rules_0_action_" field should contain "Resource"
        And the "badge_form_rules_0_action__" field should contain "Blog"
        And the "badge_form_rules_0_action___" field should contain "Post creation in a blog"
        And I follow "Delete"
        Then I wait for the confirm popup to appear
        And I should see "Deletion of a badge"
        And I should see "Are you sure you want to delete the badge Badge de test ?"
        Then I press "Delete"
        Then I should be on "/admin/badges"
        And I should see 0 ".badge_list .badge" elements
        And I should see "Badge deleted with success."

    @javascript
    Scenario: Successful creation of a badge with rule on badge awarding
        Given the admin account "JohnDoe" is created
        When I log in with "JohnDoe"/"JohnDoe"
        And I go to "/admin/badges"
        Then I should see "Platform badges"
        And I should see 0 ".badge_list .badge" elements
        And I follow "Add a badge"
        Then I should be on "/admin/badges/add"
        And I click on "#add_rule"
        Then I should see 1 "#ruleTabs li[id^=tabrule]" elements
        And I should see "Rule 1"
        And I should see "Rule details"
        And I select "Badge" from "badge_form_rules_0_action_"
        Then I should see "When the action is a badge awarding"
        And I select "Badge awarding" from "badge_form_rules_0_action__"
        And I click on "#s2id_badge_form_rules_0_badge a"
        Then I should see "Select the badge"
        And I attach the file "vendor/claroline/core-bundle/Claroline/CoreBundle/Resources/public/images/test/html5_logo.png" to "badge_form_file"
        And I fill in "badge_form_frTranslation_name" with "Badge de test"
        And I fill in "badge_form_frTranslation_description" with "C'est un badge de test"
        And I fill in tinymce "badge_form_frTranslation_criteria" with "Pour avoir ce badge de test il faut se le voir attribuer."
        And I press "Add"
        Then I should be on "/admin/badges"
        And I should see "Badge added with success."
        And I should see 1 ".badge_list .badge" elements
        And I click on ".badge_list .badge .badge_menu_link"
        Then I follow "Delete"
        And I wait for the confirm popup to appear
        And I should see "Deletion of a badge"
        And I should see "Are you sure you want to delete the badge Badge de test ?"
        Then I press "Delete"
        Then I should be on "/admin/badges"
        And I should see 0 ".badge_list .badge" elements
        And I should see "Badge deleted with success."

    @javascript
    Scenario: Creation of a badge awarding demand with validation of the awarding
        Given the admin account "JohnDoe" is created
        When I log in with "JohnDoe"/"JohnDoe"
        And I go to "/admin/badges"
        Then I should see "Platform badges"
        And I should see 0 ".badge_list .badge" elements
        And I follow "Add a badge"
        Then I should be on "/admin/badges/add"
        And I attach the file "vendor/claroline/core-bundle/Claroline/CoreBundle/Resources/public/images/test/html5_logo.png" to "badge_form_file"
        And I fill in "badge_form_frTranslation_name" with "Badge de test"
        And I fill in "badge_form_frTranslation_description" with "C'est un badge de test"
        And I fill in tinymce "badge_form_frTranslation_criteria" with "Pour avoir ce badge de test il faut se le voir attribuer."
        And I press "Add"
        Then I should be on "/admin/badges"
        And I should see "Badge added with success."
        And I should see 1 ".badge_list .badge" elements
        And I click on the 2nd ".navbar-collapse .navbar-right a.dropdown-toggle"
        Then I should see "My badges"
        And I follow "My badges"
        Then I should be on "/profile/badge/"
        And I should see "No badge."
        And I follow "Claim a badge"
        Then I should be on "/profile/badge/claim"
        And I should see "Claim badge form"
        And I fill in "#badge_claim_form_badge" with "Badge" for autocomplete
        And I wait for the suggestion box to appear
        Then I should see "Badge de test" in the suggestion box
        And I click on the 1st ".select2-results li"
        And I press "Claim"
        Then I should see "Badge claimed with success."
        And I should see "1 claim for badge"
        And I follow "1 claim for badge"
        Then I should see "Badge de test"
        And I go to "/admin/badges"
        Then I should see "1 claim for badge to examine"
        And I follow "1 claim for badge to examine"
        And I follow "Validate"
        And I wait for the confirm popup to appear
        And I should see "Validation of badge awarding"
        And I should see "Are you sure you want to award badge"
        Then I press "Award"
        Then I should be on "/admin/badges"
        And I should see "Validation of badge awarding made with success."
        And I should not see "claim for badge to examine"
        And I go to "/profile/badge"
        Then I should see 1 ".badge_list li.badge_container" elements
        And I should see "Badge de test"
        Then I go to "/admin/badges"
        And I follow "Badge de test"
        And I should see 1 "#award_users_container table tbody tr" elements
        And I should not see "Start to award this badge."
        And I follow "Removing of a badge"
        And I wait for the confirm popup to appear
        And I should see "Removing of a badge"
        And I should see "Are you sure you want to remove the badge"
        Then I press "Remove award"
        And I should see "Badge awarded remove with success."
        And I should see "Start to award this badge."
        And I go to "/profile/badge"
        Then I should see 0 ".badge_list li.badge_container" elements
        And I should see "No badge."
        And I go to "/admin/badges"
        Then I click on ".badge_list .badge .badge_menu_link"
        And I follow "Delete"
        And I wait for the confirm popup to appear
        And I should see "Deletion of a badge"
        And I should see "Are you sure you want to delete the badge Badge de test ?"
        Then I press "Delete"
        Then I should be on "/admin/badges"
        And I should see 0 ".badge_list .badge" elements
        And I should see "Badge deleted with success."

    @javascript
    Scenario: Automatic badge awarding
        Given the admin account "JohnDoe" is created
        When I log in with "JohnDoe"/"JohnDoe"
        And I go to "/admin/badges"
        And I follow "Add a badge"
        And I click on "#add_rule"
        Then I should see 1 "#ruleTabs li[id^=tabrule]" elements
        And I should see "Rule 1"
        And I should see "Rule details"
        And I select "Resource" from "badge_form_rules_0_action_"
        And I select "Blog" from "badge_form_rules_0_action__"
        And I select "Post creation in a blog" from "badge_form_rules_0_action___"
        And I attach the file "vendor/claroline/core-bundle/Claroline/CoreBundle/Resources/public/images/test/html5_logo.png" to "badge_form_file"
        And I fill in "badge_form_frTranslation_name" with "Badge de test"
        And I fill in "badge_form_frTranslation_description" with "C'est un badge de test"
        And I fill in tinymce "badge_form_frTranslation_criteria" with "Pour avoir ce badge de test il faut créer un article sur un blog."
        And I check "badge_form_automatic_award"
        And I press "Add"
        Then I should see "Badge added with success."
        And I go to personal workspace of "JohnDoe"
        And resource manager is loaded
        Then I click on ".resource-manager li.dropdown a.dropdown-toggle"
        And I click on "#icap_blog"
        And I wait for the popup to appear
        And I fill in "icap_blog_form_name" with "Blog de test"
        And I press "Ok"
        And I click on ".node-element[data-type=icap_blog]" in the resource manager
        Then I should see "Blog de test"
        Then I should see "No post."
        And I follow "New post"
        Then I should see "Add new post"
        And I fill in "icap_blog_post_form_title" with "Article de test"
        And I fill in tinymce "icap_blog_post_form_content" with "Cet article n'est qu'un simple test."
        And I press "Add"
        Then I should see "Post added with success"
        And I should see "Article de test"
        And I go to "/profile/badge"
        Then I should see 1 ".badge_list li.badge_container" elements
        And I should see "Badge de test"
        And I go to "/admin/badges"
        Then I click on ".badge_list .badge .badge_menu_link"
        And I follow "Delete"
        And I wait for the confirm popup to appear
        And I should see "Deletion of a badge"
        And I should see "Are you sure you want to delete the badge Badge de test ?"
        Then I press "Delete"
        Then I should be on "/admin/badges"
        And I should see 0 ".badge_list li.badge_container" elements
        And I should see "Badge deleted with success."
        And I go to personal workspace of "JohnDoe"
        And I click on ".nodes .dropdown[title='Blog de test'] a" in the resource manager
        And I click on ".nodes .dropdown[title='Blog de test'] .node-menu-action[data-action=delete]" in the resource manager
        And I wait for the confirm popup to appear
        Then I click on "#confirm-ok"