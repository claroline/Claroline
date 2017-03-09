############################################################
# Remote user synchronization plugin for Claroline Connect #
############################################################


---------------
| Description |
---------------

This will allow to create an authentication token that will be associated to a non-admin user.
This token will allow to authenticate the user if used in a specific URL.
The token will only be valid one time and for a defined duration (10 minutes by default)


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
"[PATH TO app.php|app_dev.php]/remote-user-synchronization/remote/user/token/generate"

* HTTP Request Method type : "POST"

* HTTP Request Content-Type : "application/json"

* POST datas format :

    {
        "client": "<Name of the client defined in Administration>",
        "token": "<Token defined in Administration>",
        "userId": <Id of an user>
    }

* Fields explanation :

    MANDATORY : client, token, userId

    FOR SECURITY PURPOSE :

        - client :
            It must match the name of a security token defined in administration.

        - token :
            It must match the token of the security token defined in administration
            associated to the client given above.

        * In addition the IP address of the client is retrieved.
          So the threesome "client", "token" and "IP address" must match a security
          token defined in administration.

    FOR USER TOKEN GENERATION :

        - userId :
            The id of the user for who you want to generate an authentication token.

* Token lifetime can be specified in platform_options.yml files with the key "remote_user_token_lifetime".
  Its value must be an integer and is evaluated in minute.
  By default it will be 10 minutes.


------------
| Response |
------------

* Success :
    
    - Status : 200
    - Body : [The generated token]

* Missing "client", "token", "userId" or no match for "client", "token" and "IP address"
  with a security token defined in administration :

    - An AccessDeniedException is thrown
    - Status : 403
    - Body : "Access Denied"

* "userId" is not valid or is associated to an administrator :

    - An AccessDeniedException is thrown
    - Status : 403
    - Body : "Access Denied"
  

-----------
| Example |
-----------

* First a security token has to be created in
  "Administration > Parameters > Security tokens management" :

    - Name : Claroline
    - IP address : 127.0.0.1
    - Token : xxxxxxxxxx

* Here is an example of the datas that have to be sent to create and associate a token to user with id "123"

    {
        "client": "Claroline",
        "token": "xxxxxxxxxx",
        "userId": 123
    }

* The request will return the generated token. For example : "8e9b6f3082bfa8be80041d206fd49831"


---------------------------------------------------------
| Usage of the generated token to authenticate the user |
---------------------------------------------------------

* URL that has to be called :
"[PATH TO app.php|app_dev.php]/remote-user-synchronization/remote/user/[userId]/token/[token]/connect/[workspaceCode]"

* Parameters explanation :

    MANDATORY : userId, token
    OPTIONAL : workspaceCode

    - userId :
        The id of the user that has to be authenticated.

    - token :
        The generated token associated to the user.

    - workspaceCode :
        The code of the workspace in which the user will be redirected to once authenticated.
        If not specified the authenticated user will be redirected to his desktop

* Success :
    The authenticated user is redirected to his desktop or to a specified workspace

* The association (user, token) doesn't exist OR token has expired OR token has already been used once :
    - An AccessDeniedException is thrown
    - Status : 403
    - Body : "Access Denied"

* Example : (based on the previous one)
    [PATH TO app.php|app_dev.php]/remote-user-synchronization/remote/user/123/token/8e9b6f3082bfa8be80041d206fd49831/connect
