Register and Configure a LinkedIn App
======================================

1. Login to the LinkedIn [Developer portal](https://developer.linkedin.com/) and click on **My Apps**
![LinkedIn developer portal home page][home_page]

2. Click on **Create Application**
![LinkedIn Create App][new_app_create]

3. Provide all necessary information in the **New Application** application form, accept the **Terms of Use** and **Submit**
![LinkedIn new App form][new_app_form]

4. Your application is now created and you are redirected to its administration page. Here you can find your **Client ID** and **Client Secret** and configure your APP. In order for your app to work properly make sure that **r_basicprofile** and **r_emailaddress** are checked. Provide also an Authorized Redirect URL in the following form:
```
    http://YOUR_DOMAIN_NAME/login/check-linkedin

    e.g. http://myclaroline.univ-lyon.fr/login/check-linkedin
```
![New App configuration][new_app_configuration]

4. Next you can copy and paste your credentials to Claroline platform:
```Administration -> Platform Settings -> Oauth -> LinkedIn```

##### Congratulations you have now registered and configured your LinkedIn App!

[home_page]: images/linkedin/linkedin_home.jpg "Developer portal"
[new_app_create]: images/linkedin/linkedin_new_app_create.jpg "Create new App"
[new_app_form]: images/linkedin/linkedin_new_app_form.jpg "Fill in your App form"
[new_app_configuration]: images/linkedin/linkedin_new_app_config_credentials.jpg "Configure your App, get your Credentials"
