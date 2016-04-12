# strophe.disco.js

strophe.disco.js is a plugin to provide Service discovery
( [XEP-0030>](http://xmpp.org/extensions/xep-0030.html) ).

There are two plugins available.

## Plugin A

This [plugin](strophejs-plugins/tree/master/disco/strophe.disco.js) allows to
send disco queries and registers handlers that respond to incoming queries.

### Usage

    var c = new Strophe.Connection('bosh-service');
    c.connect(jid,pw);
    c.disco.info(jid,callback);

#### Run Specs

use node with jasmine-node plugin to run the specs

### ToDo

- cleanup stanza specs using Strophe.Builder instead of strings

## Plugin B

The [plugin](strophejs-plugins/tree/master/disco/public/javascript/strophe.disco.js)
facilitates client and server side handling of discovery messages.

### Client Side

The plugin provides to methods (info and items) on top of the disco object that
is added to the connection. You use them as follows

    var c = new Strophe.Connection('http://localhost/xmpp-httpbind');
    c.connect('andi@psi/strophe','andi');
    c.disco.info('andi@psi/psi');

You can also pass a node, success and error handlers to the method.
The items method behaves in the same way. Just make sure that your success and
error handlers are passed after the node (if any).

### Server Side

The module adds response handlers to info and item queries.
The disco object added to the connection has members for features and identity
that will be used to populate the disco#info response.

     <iq xmlns='jabber:client' from='andi@psi/strophe' to='andi@psi/strophe2' type='result' id='4774:sendIQ'><query xmlns='http://jabber.org/protocol/disco#info'><identity name='strophe'/><feature var='http://jabber.org/protocol/disco#info'/><feature var='http://jabber.org/protocol/disco#items'/></query></iq>

You can additional nodes using addNode, e.g.
    c.disco.addNode('aNode', { items: [{node: 'aNode', name: 'aName'}]  });

and then query for them using the items method

    c1.disco.items('andi@psi/strophe', 'aNode', function(s) { console.log(Strophe.serialize(s)) ; } )

See the specs for details.

### Run Specs

To run the specs you should install jasmine-tool for nodejs via npm and update
the references to the external libraries (Strophe, jQuery) in jasmine.json.
After that you run

    $> jasmine mon

and navigate your browser to http://localhost:8124 to view the specs executing.
