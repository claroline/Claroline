Register and Configure a Windows Live App
======================================

1. Log into the [Microsoft account Developer Center](https://account.live.com/developers/applications) and click **Create application**. Name your new app, choose your language and click **I accept**.
![Windows Create App][new_app_create]

2. Your app is now created. You are redirected to a page with the basic information of your app. Here you can upload a logo and fill in some useful information about your app. When ready click **Save**.
![New app basic information][new_app_info]

3. Select **API Settings** from the left menu. Complete the form and provide with the Redirect URL in the following form:
```
    http://YOUR_DOMAIN_NAME/login/check-windows

    e.g. http://myclaroline.univ-lyon.fr/login/check-windows
```
![Api settings of new app][new_app_api_settings]

4. Select **App Settings** from the left menu. Your app's ```Client Id``` and ```Client Secret``` will be displayed. You can copy and paste your credentials to Claroline platform:
```Administration -> Platform Settings -> Oauth -> Windows Live```
![New App credentials][new_app_secrets]

##### Congratulations you have now registered and configured your Windows Live App!

[new_app_create]: images/windows/windows_new_app_create.jpg "Create new app"
[new_app_info]: images/windows/windows_new_app_info.jpg "New App general info"
[new_app_api_settings]: images/windows/windows_new_app_api_settings.jpg "Api settings for new App"
[new_app_secrets]: images/windows/windows_new_app_secrets.jpg "New app credentials"