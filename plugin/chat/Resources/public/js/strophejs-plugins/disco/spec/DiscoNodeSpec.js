
xdescribe("DiscoNode", function() {
	var node, DiscoNode = Strophe.DiscoNode, iq, c = mockConnection();
	beforeEach(function() {
		node = new DiscoNode();
		iq = {to: 'n@d/r2', from: 'n@d/r1', type: 'get', id: 'abc'};
	});

	it("#reply switches from and to, changes type to 'result'", function() {
		var req = $iq(iq), res = reply(node,req);
		expect(res.attr('to')).toEqual(iq.from);
		expect(res.attr('id')).toEqual(iq.id);
		expect(res.attr('type')).toEqual('result');
	});

	it("#reply includes first child only in response", function() {
		var req = $iq(iq).c('query', { xmlns: Strophe.NS.DISCO_INFO });
		expect(reply(node,req).find('query').attr('xmlns')).toEqual(Strophe.NS.DISCO_INFO);
		expect(reply(node,req.c('another')).find('another').length).toEqual(0);
	});

	it("#reply calls addContent(req,res) which can add elements", function() {
		var req = $iq(iq).c('query', { xmlns: Strophe.NS.DISCO_INFO });
		node.addContent = jasmine.createSpy('addContent').andCallFake(function(req,res) {
			res.c('another');
		});
		expect(reply(node,req).find('another').length).toEqual(1);
		expect(node.addContent).toHaveBeenCalled();
	});

	it("#info: this is how checking for response should look like", function() {
//		c.disco.add(Strophe.NS.DISCO_INFO);
		var req = $iq(iq).c('query', { xmlns: Strophe.NS.DISCO_INFO});
		spyon(c,'send',function(res) {
			expect(res.find('identity').attr('name')).toEqual('strophe');
			expect(res.find('feature:eq(0)').attr('var')).toEqual(Strophe.NS.DISCO_INFO);
			expect(res.find('feature:eq(1)').attr('var')).toEqual(Strophe.NS.DISCO_ITEMS);
		});
		receive(c,req);
	});

	xit("#lala - should fail", function() {
		var req = $msg();
		spyon(c,'send',function(res) {
			expect(res.find('feature:eq(1)').attr('var')).toEqual(Strophe.NS.DISCO_ITEMS);
		});
		receive(c,req);
	});
});

