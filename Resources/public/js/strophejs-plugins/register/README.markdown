# strophe.register.js

A Strophe Plugin for In-Band Registration.
( [XEP 0077](http://xmpp.org/extensions/xep-0077.html) )

## Usage

Just link the register plugin below the strophe library in your HTML head
section:

``` html
<head>
<!-- ... -->
<script type="text/javascript" src="strophe.min.js"></script>
<script type="text/javascript" src="strophe.register.js"></script>
<!-- ... -->
</head>
```

To register a JID you need to listen for REGISTER und REGISTERED in
your connection callback and use connection.register.connect() instead
of connection.connect().

On REGISTER you need to inspect the ```connection.register.fields```
object, fill in every field and call connection.register.submit().
(There may be more fields than username and password!)

On REGISTERED you can can then call connection.authenticate() if you
want to login normally with the account you just created.

You should also listen for CONFLICT, REGIFAIL and NOTACCEPTABLE to catch
failure-status of registrations.

Example for registering a new account and logging in with it:

``` javascript
var callback = function (status) {
    if (status === Strophe.Status.REGISTER) {
        // fill out the fields
        connection.register.fields.username = "juliet";
        connection.register.fields.password = "R0m30";
        // calling submit will continue the registration process
        connection.register.submit();
    } else if (status === Strophe.Status.REGISTERED) {
        console.log("registered!");
        // calling login will authenticate the registered JID.
        connection.authenticate();
    } else if (status === Strophe.Status.CONFLICT) {
        console.log("Contact already existed!");
    } else if (status === Strophe.Status.NOTACCEPTABLE) {
        console.log("Registration form not properly filled out.")
    } else if (status === Strophe.Status.REGIFAIL) {
        console.log("The Server does not support In-Band Registration")
    } else if (status === Strophe.Status.CONNECTED) {
        // do something after successful authentication
    } else {
        // Do other stuff
    }
};

connection.register.connect("example.com", callback, wait, hold);
```

After that you're logged in with a fresh jid.
