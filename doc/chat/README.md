##############
# ChatBundle #
##############

How to use
==========

Install an xmpp server (eg: prosody) 
------------------------------------

``` apt-get install prosody ```

Install an ice server (not required)
-----------------------------------

``` apt-get install resiprocate-turn-server```

Configure the xmpp server
------------------------

You need to enable bosh. For prosody, you must add these lines (amongst other things)

```vim /etc/prosody/prosody.cfg.lua```

```
allow_registration = true;
bosh_max_inactivity = 60
cross_domain_bosh = true

-- These are the SSL/TLS-related settings. If you don't want
-- to use SSL/TLS, you may comment or remove this
--ssl = {
--    key = "/etc/apache2/ssl/apache.key";
--    certificate = "/etc/apache2/ssl/apache.crt";
--}

bosh_ports = {
    {
        port = 5280;
        path = "http-bind";
    },
    {
        port = 5281;
        path = "http-bind";
        ssl = ssl
    }
}

```
Don't forget to enable the bosh module 

```
modules_enabled = {
   ...
    "bosh"; -- Enable BOSH clients, aka "Jabber over HTTP"
   ...
}

```

And set the hosts (you'll want to replace localhost by something else obviously)

```
VirtualHost "localhost"
        enabled = true -- Remove this line to enable this host

        -- Assign this host a certificate for TLS, otherwise it would use the one
        -- set in the global section (if any).
        -- Note that old-style SSL on port 5223 only supports one certificate, and will always
        -- use the global one.
        ssl = {
            key = "/etc/apache2/ssl/apache.key";
           certificate = "/etc/apache2/ssl/apache.crt";
        }

------ Components ------
-- You can specify components to add hosts that provide special services,
-- like multi-user conferences, and transports.
-- For more information on components, see http://prosody.im/doc/components

---Set up a MUC (multi-user chat) room server on conference.example.com:
Component "conference.localhost" "muc"
```

Restart prosody 

``` service prosody restart ```

Register the admin user

```
sudo prosodyctl register prosodyAdmin myDomain prosodyAdmin
```

Configure the ICE server (not required)
--------------------------------

```vim /etc/reTurn/reTurnServer.config```

```
TurnAddress = x.x.x.x
AuthenticationRealm = myHost
UserDatabaseHashedPasswords = false
```

Then create abn admin user in the following file: ```/etc/reTurn/users.txt```

Restart the server

``` /etc/init.d/resiprocate-turn-server restart ```

Configure Claroline
------------------
go to ```clarolinechatbundle/admin/chat/configure/form``` 
- host: myhost (defined in the prosody config)
- admin: prosody admin you created earlier
- password: admin password you created earlier
- muc server: conference.myhost (defined in the prosody config)
- bosh port: 5280 (prosody config: 5281 for ssl usually)
- ice server: `[{url: 'turn:x.x.x.x', credential: 'root', username: 'root'}]` (your ice server configuration as a json array)

Enable SSL
---------
Once apache and prosody are configured, you need to put your certificate at a 3rd place wich is not changeable yet. You can find where php is fetching its certificate with ```openssl_get_cert_locations```.
For me it was in ```/usr/lib/ssl/cert.pem``` This certificate contains both key (the fullchain one iirc)

Known issue
----------
Video chat doesn't always work (ice connection fails)
