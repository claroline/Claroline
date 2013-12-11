Third-party authentication - Client-side
============


Registration
------------

Third-party application that need access to the platform will first need to be register in it.

To do so a command line utility is available:

```
php app/console claroline:client:create --redirect-uri="http://www.example.com/" --grant-type="authorization_code" --grant-type="password" --grant-type="refresh_token" --grant-type="token" --grant-type="client_credentials" client_name
```

If everything went fine you should get a response from the command with something like:

```
Added a new client with public id 4_3cpk8blr9fswcggkc4gkkgok0oc0kgc8sw4gk80ksso4040wcs, secret np3j4uvzxs0wo4wk4ccggococ0k84kog8gs0so0c840oggos4
```

In this example we created a client with all possible grant types allowed, but for real use case maybe just the one needed will be enough.
The redirect uri represents a URL, where your client application is deployed.
You will get redirected here if everything went as planned, when redirection is involved in the authorization process.

This public_id and secret have to be given to the third-party applciation developers as they'll need it for further request.


There are multiple flow to authenticate third-party application.
`Token` flow is the only one needed to allow third-party application to access the platform, the other served to authenticate and authorize it.


Request format
-----------------------

All request can be `get` or `post` request, it's up to you.

When data is returned it's always in `json`.


Authorization Code flow
-----------------------

That’s the most commonly used one, recommended to authorize end customers.
A good example is the Facebook Login for websites. Here’s how it works.

The Authorization Code flow generates an authorization code when the user grants access and the OAuth2 client needs to make a subsequent request to get the access and refresh tokens.

Make the following request:

**url**:
> PROVIDER_HOST/oauth/v2/auth

**parameters**:
> * **client_id** => CLIENT_ID
> * **redirect_uri** => CLIENT_HOST
>   * should be identical to the one provided on client creation, otherwise you will get a corresponding error message.
> * **response_type** => 'code'

The page you are requesting will offer you a login, then authorization of the client permissions, once you confirm everything it will redirect you back to the url you provided in redirect_url.
In our case, redirect will look like

<pre>
CLIENT_HOST/?code=Yjk2MWU5YjVhODBiN2I0ZDRkYmQ1OGM0NGY4MmUyOGM2NDQ2MmY2ZDg2YjUxYjRiMzAwZTY2MDQxZmUzODg2YQ
</pre>

I’ll refer to this long code parameter as CODE in the future. This code is stored on the Provider side, and once you request for the token, it can uniquely identify the client which made request and the user.

It’s time to request the token:

**url**:
> PROVIDER_HOST/oauth/v2/token

**parameters**:
> * **client_id** => CLIENT_ID
> * **client_secret** => CLIENT_SECRET
> * **redirect_uri** => CLIENT_HOST
>   * should be identical to the one provided on client creation, otherwise you will get a corresponding error message.
> * **grant_type** => authorization_code
> * **code** => CODE

Most probably this request will fail. That’s because CODE expires rather quickly. Fear not, just request first URL, repeat the process, prepare the second url in the text editor of your choice, copy in the code rather quickly, and you will get the desired result.

It’s a JSON which contains access_token and looks like this

```json
{
    "access_token":"NjlmNDNiZTU4ZDY3ZGFlYTI5MGEzNDcxZWVmZDU4Y2E1NGJmZTJlMjNjNzc2M2E0MmZlZTk2ZjliMWE0MDQyNw",
    "expires_in":3600,
    "token_type":"bearer",
    "scope":null,
    "refresh_token":"ZGU2NzlhOTQ2MmRlY2YyYjAyMjBkYmJmMmJhMDllNTgyNmJkNmQxOWZlNGQ4NzczY2RiMThlNmRhMjBiYjFjNg"
}
```

this suggests that access_token expires in 3600 seconds, and to refresh it you have the refresh token. We will discuss how to handle that later on this chapter.


Implicit Grant flow
-------------------

It’s similar to Authorization Code grant, it’s just a bit simpler.
You just need to make only one request, and you will get the access_token as a part of redirect URL, there’s no need for second response.
Refresh tokens are not supported in this flow.
That’s for the situations where you trust the user and the client, but you still want the user to identify himself in the browser.

**url**:
> PROVIDER_HOST/oauth/v2/auth

**parameters**:
> * **client_id** => CLIENT_ID
> * **redirect_uri** => CLIENT_HOST
> * **response_type** => 'token'

then you will get redirected to

<pre>
CLIENT_HOST/#access_token=YWZhZWQ5NjQxOTI2ODJmZWE4YjJiYmExZTIxZmE5OWUxOWZjZjgwZDFlZWMwMjkyZDQwZWU1NWI4YWIzODllNQ&expires_in=3600&token_type=bearer&refresh_token=YzQ1YjRhODk2YzJiYTZmMzNiNjI5ZjI2MDI3ZmMwMDg3MjkxMDdhYmE5YjBlYzRlZmM2M2Q0NTM3ZjFmZDZiYQ
</pre>


Password flow
-------------

Let’s say you have no luxury of redirecting user to some website, then handle redirect call, all you have is just an application which is able to send HTTP requests.
And you still want to somehow authenticate user on the server side, and all you have is username and password.

Request:

**url**:
> PROVIDER_HOST/oauth/v2/token

**parameters**:
> * **client_id** => CLIENT_ID
> * **client_secret** => CLIENT_SECRET
> * **grant_type** => 'password'
> * **username** => USERNAME
> * **password** => PASSWORD

Response:

```json
{
    "access_token":"MjY1MWRhYTAyZDZlOTEyN2EzNTg4MGMwMTcyYjczY2Y0MWI3NzZjODc1OGM2NDdjODgxZjY3YzEyMDdhZjU0Yg",
    "expires_in":3600,
    "token_type":"bearer",
    "scope":null,
    "refresh_token":"MDNmNzBmNWQ2NzdhYWVmYjE2NjI3ZjAyZTM4Y2Q1NDRiNDY1YjUyZGE1ZDk0ODZjYmU0MDM0NTQxNjhiZmU3ZA"
}
```

Client Credentials flow
-----------------------

This one is the most simplistic flow of them all. You just need to provide CLIENT_ID and CLIENT_SECRET to get back an access token.

**url**:
> PROVIDER_HOST/oauth/v2/token

**parameters**:
> * **client_id** => CLIENT_ID
> * **client_secret** => CLIENT_SECRET
> * **grant_type** => 'client_credentials'

Response will be

```json
{
    "access_token":"YTk0YTVjZDY0YWI2ZmE0NjRiODQ4OWIyNjZkNjZlMTdiZGZlNmI3MDNjZGQwYTZkMDNiMjliNDg3NWYwZWI0MQ",
    "expires_in":3600,
    "token_type":"bearer",
    "scope":"user",
    "refresh_token":"ZDU1MDY1OTc4NGNlNzQ5NWFiYTEzZTE1OGY5MWNjMmViYTBiNmRjOTNlY2ExNzAxNWRmZTM1NjI3ZDkwNDdjNQ"
}
```

Refresh flow
------------

The `access_tokens` have a lifetime of one hour, after which they will expire.
With every `access_token` you were provided a `refresh_token`. You can exchange `refresh_token` and get a new pair of `access_token` and `refresh_token`.

**url**:
> PROVIDER_HOST/oauth/v2/token

**parameters**:
> * **client_id** => CLIENT_ID
> * **client_secret** => CLIENT_SECRET
> * **grant_type** => 'refresh_token'
> * **refresh_token** => REFRESH_TOKEN

response

```json
{
    "access_token":NEW_ACCESS_TOKEN,
    "expires_in":3600,
    "token_type":"bearer",
    "scope":"user",
    "refresh_token":"NEW_REFRESH_TOKEN"
}
```


[index documentation][1]

[1]: ../index.md