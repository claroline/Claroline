var helper = (function() {
	function logStanza(s) {
		if (s.length && s.length === 1) { s = s[0]; }
		if (s.tree) { s = s.tree(); }
		console.log(Strophe.serialize(s));
	}

	function str(builder) {
		if (builder.tree) {
			return $(Strophe.serialize(builder.tree()));
		}
		return $(Strophe.serialize(builder));
	}
	function receive(c,req) {
		c._dataRecv(createRequest(req));
		expect(c.send).toHaveBeenCalled();
	}

	function spyon (obj,method, cb)  {
		spyOn(obj,method).andCallFake(function(res) {
			res = str(res);
			cb.call(this,res);
		});
	}

	function mockConnection() {
		var c = new Strophe.Connection();
		c.authenticated = true;
		c.jid = 'n@d/r2';
		c._processRequest = function() {};
		c._changeConnectStatus(Strophe.Status.CONNECTED);
		return c;
	}

	 function createRequest(iq) {
		iq = typeof iq.tree == "function" ? iq.tree() : iq;
		var req = new Strophe.Request(iq, function() {});
		req.getResponse = function() { 
			var env = new Strophe.Builder('env', {type: 'mock'}).tree();
			env.appendChild(iq);
			return env;
		};
		return req;
	}

	return {
		createRequest: createRequest,
		mockConnection: mockConnection,
		receive: receive,
		spyon: spyon,
		str: str
	};
})();

