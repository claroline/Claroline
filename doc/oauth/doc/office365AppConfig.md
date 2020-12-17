ATTENTION!
==============

If you're using your own Azure Active Directory APP the steps are not the same as the ones mentioned below. 
However they give you a hint on how to configure your APP. (See step 3 below for **Redirect URIs** configuration).

You'll also notice that under the configuration options (```Administration -> Platform Settings -> Oauth -> Office 365```) 
there is a **Domain** field. This is for your **Azure AD tenant ID domain**.
**The Domain field should be left blank if you're not using an Azure Oauth APP and are configuring an APP as explained below** 

Register and Configure an Office 365 App
=========================================

1. Log into the [Microsoft account Developer Center](https://apps.dev.microsoft.com/#/appList) and click **Add an app** next to the *Converged applications*. Name your new app and then **Create application**.
![Office 365 Create App][new_app_create]

2. Your app is now created. You are redirected to a page with your app's configuration.
Your app's ```Client Id``` (Application Id) and ```Client Secret``` (Application Secrets) are available here.
Click on **Generate New Password** to get a ```Client Secret```.
**NB: Your password is only displayed to you ONCE. Afterwards it stays hidden, consider to store it someplace securely.**
You can copy and paste your credentials to Claroline platform:
```Administration -> Platform Settings -> Oauth -> Office 365```
**NB: The Domain field should be left blank. It's only there for Azure AD APPs.**

3. Under the *Platforms* section if it's empty click on **Add Platform** and choose **Web**.
Then you have a panel appearing under the **Platforms** section. Fill in the **Redirect URIs** following the format below:
``` 
    http://YOUR_DOMAIN_NAME
    
    http://YOUR_DOMAIN_NAME/login/check-o365

    e.g. http://myclaroline.univ-lyon.fr
    
    and  http://myclaroline.univ-lyon.fr/login/check-o365
```
![Api settings of new app][new_app_api_settings]

4. Fill in the profile information of your app (optional) and then click **Save**. 
**Do not forget to Save your app every time you perform any changes on it**

##### Congratulations you have now registered and configured your Office 365 App!

[new_app_create]: images/office365/office365_new_app_create.jpg "Create new app"
[new_app_api_settings]: images/office365/office365_new_app_api_settings.jpg "Api settings for new App"