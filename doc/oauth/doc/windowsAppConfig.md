Register and Configure a Windows Live App
======================================

1. Log into the [Microsoft account Developer Center](https://apps.dev.microsoft.com/#/appList) and click **Add an app** next to the *Live SDK applications*. Name your new app and then **Create application**.
![Windows Create App][new_app_create]

2. Your app is now created. You are redirected to a page with your app's configuration.
Your app's ```Client Id``` (Application Id) and ```Client Secret``` (Application Secrets) are available here. 
You can copy and paste your credentials to Claroline platform:
```Administration -> Platform Settings -> Oauth -> Windows Live```

3. Under the *Platforms* section if it's empty click on **Add Platform** and choose **Web**.
Then you have a panel appearing under the **Platforms** section. Fill in the **Target Domain** with your domain info, 
e.g. ```http://myclaroline.univ-lyon.fr``` and then add the **Redirect URIs** following the format below:
``` 
    http://YOUR_DOMAIN_NAME
    
    http://YOUR_DOMAIN_NAME/login/check-windows

    e.g. http://myclaroline.univ-lyon.fr
    
    and  http://myclaroline.univ-lyon.fr/login/check-windows
```
![Api settings of new app][new_app_api_settings]

4. Fill in the profile information of your app (optional) and then click **Save**. 
**Do not forget to Save your app every time you perform any changes on it**

##### Congratulations you have now registered and configured your Windows Live App!

[new_app_create]: images/windows/windows_new_app_create.jpg "Create new app"
[new_app_api_settings]: images/windows/windows_new_app_api_settings.jpg "Api settings for new App"