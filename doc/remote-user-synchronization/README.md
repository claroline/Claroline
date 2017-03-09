############################################################
# Remote user synchronization plugin for Claroline Connect #
############################################################


---------------
| Description |
---------------

This plugin defines a POST method that can be called by an allowed remote client.
The method defines first if the client is authorized by checking the given token with
the security tokens list defined in the platform administration parameters
and then, if the client is authorized, creates or updates an user depending on given datas.

The user datas that can be modified (or simply created) by this method are :
    - The username
    - The first name
    - The last name
    - The email address
    - The password
    - The registrations to workspaces


----------------
| Requirements |
----------------

* A security token must be created and configured in "Administration > Parameters > Security tokens management".

    This security token is defined by :
    - Name : The name of the client
    - IP address : The IP address of the client
    - Token : The security token


---------
| Usage |
---------

* URL that has to be called :
"[PATH TO app.php|app_dev.php]/remote-user-synchronization/remote/user/sync"

* HTTP Request Method type : "POST"

* HTTP Request Content-Type : "application/json"

* POST datas format :

    {
        "client": "<Name of the client defined in Administration>",
        "token": "<Token defined in Administration>",
        "username": "<Username>",
        "firstName": "<First name>",
        "lastName": "<Last name>",
        "email": "<Email address>",
        "password": "<Password>",
        "workspaces":
        [
            {
                "<Workspace code>": "<Translation key of a workspace role>"
            },
            {
                "<Workspace code>": "<Translation key of a workspace role>"
            },
            {
                "<Workspace code>": "<Translation key of a workspace role>"
            },
            ...
        ],
        "workspacesAddOnly": [ 1 | 0 ]
        "userId": <Id of an user>
    }

* Fields explanation :

    MANDATORY : client, token, username, firstName, lastName, email, password (for creation)
    OPTIONAL : password (for update), workspaces, workspacesAddOnly, userId

    FOR SECURITY PURPOSE :

        - client :
            It must match the name of a security token defined in administration.

        - token :
            It must match the token of the security token defined in administration
            associated to the client given above.

        * In addition the IP address of the client is retrieved.
          So the threesome "client", "token" and "IP address" must match a security
          token defined in administration.

    FOR USER SYNCHRONIZATION :

        - username, firstName, lastName, email, password :
            These fields are simply used to filled the corresponding properties of
            the user who is created or updated.

        - workspaces :
            It defines the list of the workspaces where the user is registered.
            The user is registered to workspaces defined in the list and unregistered
            from those not present (Excepted the workspaces whose the user is the
            creator/owner).
            If the "workspaces" field is not defined the user will be unregistered
            from all the workspaces he is registered to (Excepted the workspaces whose
            the user is the creator/owner).
            This array is composed of associative arrays of the following format :

                { <Worspace code>: <Translation key of a workspace role> }
            
                * The associative array can only contain 1 element.
                * The key of the associative array is the code of a workspace.
                * The value of the associative array is the translation key of
                  a role of the workspace whose code matches the key of the associative
                  array.

        -workspacesAddOnly :
            If this field is set the synchronized user will be unregistered from no workspace.
      - userId :
            If defined the user whose id is equal to given "userId" is updated.
            Otherwise a new user is created.


------------
| Response |
------------

* Success :
    
    - Status : 200
    - Body : [Id of the created/synced user]
    - The created/updated user session cookie is available in the header of the
      response

* Missing "client", "token" or no match for "client", "token" and "IP address"
  with a security token defined in administration :

    - An AccessDeniedException is thrown
    - Status : 403
    - Body : "Access Denied"

* Missing "username", "firstName", "lastName", "email" :

    - Status : 400
    - Body : "Bad Request"

* Missing "password" for creation :

    - Status : 400
    - Body : "Bad Request"

* "userId" is defined but associated user cannot be found in the platform :

    - Status : 404
    - Body : "Not found"

* User cannot be created or updated because of invalid data format or unicity
  constraint :

    - Status : 400
    - Body : "User edition error"
  

-----------
| Example |
-----------

* First a security token has to be created in
  "Administration > Parameters > Security tokens management" :

    - Name : Claroline
    - IP address : 127.0.0.1
    - Token : xxxxxxxxxx

* Then here is an example of the datas that have to be sent to create a new user
  and register him to workspace "Course 1 (C001)" with role "collaborator" and
  workspace "Course 2 (C002)" with custom role "custom-role-C002" :

    {
        "client": "Claroline",
        "token": "xxxxxxxxxx",
        "username": "JohnDoe",
        "firstName": "John",
        "lastName": "Doe",
        "email": "jonh.doe@claroline.net",
        "password": "xyz123",
        "workspaces":
        [
            {
                "C001": "collaborator"
            },
            {
                "C002": "custom-role-C002"
            }
        ]
    }

  The id of the newly created user is returned as response. His user session cookie
  is available in the header of the response.
  For this example, the returned value is 12.

* Here is an example of the datas that have to be sent to update password of
  an existing user and register him to workspace "Course 3 (C003)" with role
  "manager" and unregister him from workspace "Course 2 (C002)" :

    {
        "client": "Claroline",
        "token": "xxxxxxxxxx",
        "username": "JohnDoe",
        "firstName": "John",
        "lastName": "Doe",
        "email": "jonh.doe@claroline.net",
        "password": "new-password-123",
        "workspaces":
        [
            {
                "C001": "collaborator"
            },
            {
                "C003": "manager"
            }
        ],
        "userId": 12
    }

* Here is an example of the datas that have to be sent to update an existing user
  and register him to workspace "Course 4 (C004)" with role "collaborator"
  and without unregistering him from other workspaces :

    {
        "client": "Claroline",
        "token": "xxxxxxxxxx",
        "username": "JohnDoe",
        "firstName": "John",
        "lastName": "Doe",
        "email": "jonh.doe@claroline.net",
        "workspaces":
        [
            {
                "C004": "collaborator"
            }
        ],
        "workspacesAddOnly": 1,
        "userId": 12
    }