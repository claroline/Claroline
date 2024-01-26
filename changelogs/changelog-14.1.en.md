# New Resources/Features:
- Flashcards: A new resource based on the Leitner system, allowing the creation of flashcard lists for learners to memorize.
- SCORM Export: This new feature added in the Claroline version distributed by Forma-Libre enables users to export an activity space as a SCORM package. The SCORM can then be imported into another LMS to facilitate content distribution across platforms.
- Addition of a statistical view on Database resources and the ability to export them. The resource thus becomes the preferred choice for conducting surveys and other forms.

# Major Changes:
- Changes in registration procedures: merging of two actions to simplify the process. 
- New users of a workspace are automatically added to the default role. An optional checkbox allows for the choice of a role nonetheless.
- Addition of options to configure access to confidential fields on custom training registration forms, profiles, and database records.
- Simplification of presence tracking in cursus.
- New import feature to clear all groups/users enrolled in a workspace.
- API Redesign : normalization of URLs to be detailed.
    - Support for auto-incrementing IDs is deleted :
        - IDs exposed by the API in the `autoId` property can't be used to call the API. UUIDs (properties ID) or other object identification methods (i.e:workspace code, user mail...) have to be used.
        - Deleted the `/exist` endpoint for all objects :
14.0
```
GET /apiv2/{entityName}/exist/{field}/{value}
exemple : /apiv2/user/exist/id/e12b480d-c934-4839-b0d0-c4805ffe5012
```
14.1
```
HEAD /apiv2/{entityName}/{field}/{value}
exemple : /apiv2/user/id/e12b480d-c934-4839-b0d0-c4805ffe5012
```
- 
    - Deleted the endpoint `/find` for all objects :
        - For searches using the identification method, use : `GET/HEAD /apiv2/{entityName}/{field}/{value}`
        - For searches using filters, use : `GET /apiv2/{entityName}` with `filters[myFilter]`  in the string query.g.
        
Reminder : The list of all endpoints of the API is available in the Administration menu > Integrations > Claroline API 

# Minor Changes:
- Automatic thumbnails for Peertube and YouTube resources.
- The "hide" option moved to "Display" instead of "Access Restriction".
- Display of a banner in forum topics.
- New List Widgets:
    - List of teams in the space and "My Teams".
    - List of groups and "My Groups".
    - List of roles and "My Roles".
    - List of members of my teams (with a filter on team names).
- A workspace linked to a training displays the training page instead of a generic message if the user is not yet enrolled in the space.
- Addition of template choice on sessions
- Addition of a Database field (in Resources and Training) allowing the display of a fixed text block within a form to improve its layout and guidance.
