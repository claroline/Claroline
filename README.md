OAuthBundle
============

Provides a plugin for Claroline Connect platform that ennables users to connect through social login (facebook, twitter, google, linkedin)

Installation
-------------

Via Composer:
    ```
        composer install icap/oauth-bundle
    ```

Then run the `php app/console claroline:update` command to install plugin to Claroline platform

Configuration
--------------

In order to enable 3rd party connection (social login) using the OAuth service you will need to generate a pair of **App key** + **App Secret** for every provider available (for the moment _facebook_, _twitter_, _google_ and _linkedin_)

Click on the following links to learn how to configure your App for every provider and eventually retrieve your App key & secret

- [Facebook](Resources/doc/facebookAppConfig.md)
- [Twitter](Resources/doc/twitterAppConfig.md)
- [Google](Resources/doc/googleAppConfig.md)
- [LinkedIn](Resources/doc/linkedinAppConfig.md)

Once you've created your App and got your keys, it's time to enable login to your Claroline platform.

To enable your social login, let's say _facebook login_ for example:

1. Head over to _Administration_ -> _Platform parameters_
2. Click on **Oauth** option
3. Choose the provider you wish to enable/configure (e.g. _facebook_)
4. Fill in the form with your _application id (App key)_ and your _secret (App secret)_, check _activate_ and then save

Your social login is now enabled. You can test it on your login page.

##### ATTENTION! You need to have administration priviledges in order to set any platform parameters

If your App configuration is correct then you should be able to connect using the external login. If you encounter any issues please verify your configuration both in the App and in the platform.

If you are still having difficulty connecting through your App do not hesitate to contact us.
