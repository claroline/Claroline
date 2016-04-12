function connect() {
	var c = new Strophe.Connection('http://localhost/xmpp-httpbind');
	c.connect('asdf@psi/strophe','asdf');
	return c;
}
function epic() {
	var html = "<div><h3>Epic</h3></div>";
	jQuery(html).epic({conn: connect()}).appendTo('body');
}

var createRequest = function(iq) {
	iq = typeof iq.tree == "function" ? iq.tree() : iq;
	var req = new Strophe.Request(iq, function() {});
	req.getResponse = function() { 
		var env = new Strophe.Builder('env', {type: 'mock'}).tree();
		env.appendChild(iq);
		return env;
	};
	return req;
};

function clear(stanza) {
	if (stanza.tree) {
		stanza = stanza.tree();
	}
	if (stanza.removeAttribute) {
		stanza.removeAttribute('id');
		return Strophe.serialize(stanza);
	}
	return stanza;
}
var stanzas = {
	info: {
		request: "<iq to='n@d/r' type='get' xmlns='jabber:client'><query xmlns='http://jabber.org/protocol/disco#info'/></iq>",
		request_with_node: "<iq to='n@d/r' type='get' xmlns='jabber:client'><query xmlns='http://jabber.org/protocol/disco#info' node='aNode'/></iq>",
		response: "<iq to='null' type='result' xmlns='jabber:client'><query xmlns='http://jabber.org/protocol/disco#info'><identity name='strophe'/><feature var='http://jabber.org/protocol/disco#info'/><feature var='http://jabber.org/protocol/disco#items'/></query></iq>",
		response_with_node: "<iq to='null' type='result' xmlns='jabber:client'><query xmlns='http://jabber.org/protocol/disco#info' node='aNode'><identity>aNode</identity><feature var='a'/><feature var='b'/></query></iq>",
		response_not_found: "<iq to='null' type='result' xmlns='jabber:client'><query xmlns='http://jabber.org/protocol/disco#info' node='aNode'><error type='cancel'><item-not-found xmlns='urn:ietf:params:xml:ns:xmpp-stanzas'/></error></query></iq>"
	},
	items: {
		request: "<iq to='n@d/r' type='get' xmlns='jabber:client'><query xmlns='http://jabber.org/protocol/disco#items'/></iq>",
		request_with_node: "<iq to='n@d/r' type='get' xmlns='jabber:client'><query xmlns='http://jabber.org/protocol/disco#items' node='aNode'/></iq>",
		response: "<iq to='null' type='result' xmlns='jabber:client'><query xmlns='http://jabber.org/protocol/disco#items'/></iq>",
		response_with_node: "<iq to='null' type='result' xmlns='jabber:client'><query xmlns='http://jabber.org/protocol/disco#items' node='aNode'><item name='aNother' node='aNotherNode'/></query></iq>"
	},
	commands: {
		response_node: "<iq to='null' type='result' xmlns='jabber:client'><query xmlns='http://jabber.org/protocol/disco#items' node='http://jabber.org/protocol/commands'><item jid='' node='aNode' name='aName'/></query></iq>",
		response_node_completed: "<iq to='null' type='result' xmlns='jabber:client'><command xmlns='http://jabber.org/protocol/commands' node='aNode' status='completed'/></iq>",
		response_empty: "<iq to='null' type='result' xmlns='jabber:client'><query xmlns='http://jabber.org/protocol/disco#items' node='http://jabber.org/protocol/commands'/></iq>",
		response_node_not_found: "<iq to='null' type='result' xmlns='jabber:client'><command xmlns='http://jabber.org/protocol/commands' node='aNode'><error type='cancel'><item-not-found xmlns='urn:ietf:params:xml:ns:xmpp-stanzas'/></error></command></iq>",
		response_not_found: "<iq to='null' type='result' xmlns='jabber:client'><query xmlns='http://jabber.org/protocol/disco#info' node='http://jabber.org/protocol/commands'><error type='cancel'><item-not-found xmlns='urn:ietf:params:xml:ns:xmpp-stanzas'/></error></query></iq>",
		execute: "<iq to='n@d/r' type='set' xmlns='jabber:client'><command xmlns='http://jabber.org/protocol/commands' node='aCmd' action='execute'/></iq>"
	}
};
