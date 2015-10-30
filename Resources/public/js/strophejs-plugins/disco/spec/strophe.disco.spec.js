var createRequest = helper.createRequest, mockConnection = helper.mockConnection,
	spyon = helper.spyon, receive = helper.receive;

describe("Strophe.disco#info", function() {
	var c, iq, successHandler, errorHandler;
	beforeEach(function() {
		c = mockConnection();
		iq = {to: 'n@d/r2', from: 'n@d/r1', type: 'get', id: 'abc'};
		successHandler = jasmine.createSpy('successHandler');
		errorHandler = jasmine.createSpy('errorHandler');
	});

	it("sends disco#info", function() {
		spyon(c,'send', function(req) {
			expect(req.find('query').attr('xmlns')).toEqual(Strophe.NS.DISCO_INFO);
		});
		c.disco.info('n@d/r');
	});

	it("sends disco#info to node", function() {
		spyon(c,'send',function(req) {
			expect(req.find('query').attr('node')).toEqual('aNode');
		});
		c.disco.items('n@d/r','aNode');
	});

	it("sends disco#info and calls success callback", function() {
		spyon(c,'send',function(req) {
			var res = $iq({type: 'result', id: req.attr('id')});
			c._dataRecv(createRequest(res));
		});
		c.disco.info('n@d/r', successHandler, errorHandler);
		expect(successHandler).toHaveBeenCalled();
	});

	it("sends disco#info and calls error callback", function() {
		spyon(c,'send',function(req) {
			var res = $iq({type: 'error', id: req.attr('id')});
			c._dataRecv(createRequest(res));
		});
		c.disco.info('n@d/r', successHandler, errorHandler);
		expect(errorHandler).toHaveBeenCalled();
	});

	it("responds to disco#info", function() {
		var req = $iq(iq).c('query', { xmlns: Strophe.NS.DISCO_INFO});
		spyon(c,'send',function(res) {
			expect(res.find('identity').attr('name')).toEqual('strophe');
			expect(res.find('feature:eq(0)').attr('var')).toEqual(Strophe.NS.DISCO_INFO);
			expect(res.find('feature:eq(1)').attr('var')).toEqual(Strophe.NS.DISCO_ITEMS);
		});
		receive(c,req);
	});

	it("responds to disco#info with node", function() {
		var req = $iq(iq).c('query', { xmlns: Strophe.NS.DISCO_INFO, node: 'aNode' });
		c.disco.addNode('aNode', { identity: { name: 'aNode'}, features: { 'aFeature': '' } });
		spyon(c,'send',function(res) {
			expect(res.find('identity').attr('name')).toEqual('aNode');
			expect(res.find('feature:eq(0)').attr('var')).toEqual('aFeature');
		});
		receive(c,req);
	});

	it("responds to disco#info with not found for non existing node", function() {
		var req = $iq(iq).c('query', { xmlns: Strophe.NS.DISCO_INFO, node: 'aNode' });
		spyon(c,'send',function(res) {
			expect(res.find('error').attr('type')).toEqual('cancel');
			expect(res.find('item-not-found').attr('xmlns')).toEqual('urn:ietf:params:xml:ns:xmpp-stanzas');
		});
		receive(c,req);
	});
});

describe("Strophe.disco#items", function() {
	var c, iq, successHandler, errorHandler;
	beforeEach(function() {
		c = mockConnection();
		iq = {to: 'n@d/r2', from: 'n@d/r1', type: 'get', id: 'abc'};
		successHandler = jasmine.createSpy('successHandler');
		errorHandler = jasmine.createSpy('errorHandler');
	});

	it("sends disco#items", function() {
		spyon(c,'send', function(req) {
			expect(req.find('query').attr('xmlns')).toEqual(Strophe.NS.DISCO_ITEMS);
		});
		c.disco.items('n@d/r');
	});

	it("sends disco#items to node", function() {
		spyon(c,'send',function(req) {
			expect(req.find('query').attr('node')).toEqual('aNode');
		});
		c.disco.items('n@d/r','aNode');
	});

	it("sends disco#items and calls success callback", function() {
		spyon(c,'send',function(req) {
			var res = $iq({type: 'result', id: req.attr('id')});
			c._dataRecv(createRequest(res));
		});
		c.disco.items('n@d/r', successHandler, errorHandler);
		expect(successHandler).toHaveBeenCalled();
	});

	it("sends disco#items and calls error callback", function() {
		spyon(c,'send',function(req) {
			var res = $iq({type: 'error', id: req.attr('id')});
			c._dataRecv(createRequest(res));
		});
		c.disco.items('n@d/r', successHandler, errorHandler);
		expect(errorHandler).toHaveBeenCalled();
	});

	it("responds to disco#items", function() {
		var req = $iq(iq).c('query', { xmlns: Strophe.NS.DISCO_ITEMS});
		spyon(c,'send',function(res) {
			expect(res.find('items').length).toEqual(0);
		});
		receive(c,req);
	});

	it("responds to disco#items with node", function() {
		var req = $iq(iq).c('query', { xmlns: Strophe.NS.DISCO_ITEMS, node: 'aNode' });
		c.disco.addNode('aNode', { items: [{node: 'aNode', name: 'aName'}]  });
		spyon(c,'send',function(res) {
			expect(res.find('item:eq(0)').attr('jid')).toEqual('n@d/r2');
			expect(res.find('item:eq(0)').attr('node')).toEqual('aNode');
			expect(res.find('item:eq(0)').attr('name')).toEqual('aName');
		});
		receive(c,req);
	});

	it("responds to disco#info with not found for non existing node", function() {
		var req = $iq(iq).c('query', { xmlns: Strophe.NS.DISCO_ITEMS, node: 'aNode' });
		spyon(c,'send',function(res) {
			expect(res.find('error').attr('type')).toEqual('cancel');
			expect(res.find('item-not-found').attr('xmlns')).toEqual('urn:ietf:params:xml:ns:xmpp-stanzas');
		});
		receive(c,req);
	});
});
//var c1 = new Strophe.Connection('http://localhost/xmpp-httpbind');
//c1.connect('asdf@psi/c1', 'asdf');
//
//var c2 = new Strophe.Connection('http://localhost/xmpp-httpbind');
//c2.connect('asdf@psi/c2', 'asdf');
