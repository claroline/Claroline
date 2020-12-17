Register and Configure a Facebook App
======================================

1. Login to [Facebook](https://www.facebook.com/)

2. Go to [Facebook Developers Apps](https://developers.facebook.com/apps). You'll need Facebook developer account to get started. If you don't have one just upgrade your personal Facebook account to a Facebook Developer account.

3. Click the **Create a New App** button
![Create App using button][new_app_button]
or via the dropdown menu **My Apps** choose **Add a new App**
![Create App using dropdown menu][new_app_menu]

4. When asked to choose a platform, select **Basic setup**
![Choose basic setup][new_app_platform]

5. Provide a **Display Name** for your app, choose **Apps for pages** Category, and click **Create App ID**
![App display name and category][new_app_properties]

6. Complete the **Security Check**
![Security check][new_app_security]

7. Your App is now created! Copy the `App ID` and `App Secret` from the **Dashboard** page
You can paste them to Claroline:
```Administration -> Platform Settings -> Oauth -> Facebook```
![App ID and Secret][new_app_id_secret]

8. Now you need to configure your app. Go to **Settings** and in the **Basic** tab provide a valid **Contact Email** and click on **Save Changes**
![Fill contact email][new_app_fill_email]
Go to the **Advanced** tab and scroll down to **Client OAuth Settings**. Enable the option **Embedded Browser OAuth Login** and in the **Valid OAuth redirect URIs** enter the following URL:
```
    http://YOUR_DOMAIN_NAME/login/check-facebook

    e.g. http://myclaroline.univ-lyon.fr/login/check-facebook
```

9. Last, got to **App Review** and set **Do you want to make this app and all its live features available to the general public?** option to **Yes** in order to publish your App
![Publish App][new_app_publish]

##### Congratulations you have now registered and configured your Facebook App!

[new_app_menu]: images/facebook/fb_new_app_menu.jpg "New app via dropdown menu"
[new_app_button]: images/facebook/fb_new_app_new_button.jpg "New app using button"
[new_app_platform]: images/facebook/fb_new_app_choice.jpg "Choose basic setup when asked for App platform"
[new_app_properties]: images/facebook/fb_new_app_properties.jpg "Fill in platform name and select App for pages in popup"
[new_app_security]: images/facebook/fb_security_check.jpg "Complete Security Check"
[new_app_id_secret]: images/facebook/fb_app_id_secret.jpg "Your App Id and Secret"
[new_app_fill_email]: images/facebook/fb_app_fill_email.jpg "Fill in your contact email and Save options"
[new_app_enable_browser]: images/facebook/fb_app_enable_browser_add_redirect.jpg "Enable browser and add redirect URI, Save"
[new_app_publish]: images/facebook/fb_app_publish_app.jpg "Publish your App"