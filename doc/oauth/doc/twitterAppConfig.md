Register and Configure a Twitter App
======================================

1. Login to [Twitter](https://www.twitter.com/)

2. [Create a new Twitter App](https://apps.twitter.com/app/new). Provide your Application Details: **Name**, **Description**, **Website** and **Callback URL**. Your Callback URL looks like the following:
```
    http://YOUR_DOMAIN_NAME/login/check-twitter

    e.g. http://myclaroline.univ-lyon.fr/login/check-twitter
```
![New App form][new_app_form]

3. Once your App is created, go to **Settings** tab and make sure **Allow this application to be used to Sign in with Twitter** option is checked.
You can also provide an **icon** for your App as well as your **organization's** name and **website**. Update settings.
![App Settings][new_app_settings]

4. Go to **Keys and Access Tokens** tab to copy your App ID and Secret
You can paste them to Claroline:
```Administration -> Platform Settings -> Oauth -> Twitter```
![App ID and Secret][new_app_id_secret]

##### Congratulations you have now registered and configured your Twitter App!

[new_app_form]: images/twitter/twitter_app_new_form.jpg "New App form"
[new_app_id_secret]: images/twitter/twitter_app_id_secret.jpg "Your App Id and Secret"
[new_app_settings]: images/twitter/twitter_settings_icon_organization.jpg "Provide icon and organization data"
