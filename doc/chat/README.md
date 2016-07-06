##############
# ChatBundle #
##############

Requirements
------------
- Install a XMPP server (e.g. Prososdy)
- Configure XMPP server & set up a BOSH server
- Create a XMPP user (e.g. claroline)

Before usage
------------
- Add these 2 lines in "app/config/platform_options.yml" file :
    * chat_admin_username: [username of the XMPP user created before]
    * chat_admin_password: [password of the XMPP user created before]
- In Chat management admin tool :
    * Configure Chat plugin (Configuration)
    * Create XMPP account for users (Users management)