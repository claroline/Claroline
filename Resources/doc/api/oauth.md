Oauth Connection
=================
- [Client generation](#client-generation)
- [Oauth connection](#oauth-connection)
- [Api usage](#api-usage)

Client generation
-----------------
- Go into administration => parameters => oauth => claroline (CLIENT_HOST/admin/oauth/).
- Create a new client here.

Oauth connection
----------------
- request this url: CLIENT_HOST/oauth/v2/token?client_id=CLIENT_ID&client_secret=CLIENT_SECRET&grant_type=client_credentials
- Store the returned access_token somewhere (you'll need it later).

Oauth refresh
-------------
- TODO

Api usage
---------
- Once you have your access token, you can access the api by passing the access_token as an url parameter.

> CLIENT_HOST/api/path/to/url.json?access_token=TOKEN
