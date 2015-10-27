describe("Roster", function() {
	var conn, roster;
	beforeEach(function() {
		conn = new Strophe.Connection();
		roster = conn.roster;
		conn._changeConnectStatus(Strophe.Status.CONNECTED);
		conn.authenticated = true;
	});


	it("adds contacts on presence stanza", function() {
		var pres = $pres({from: 'n@d/r'});
		conn._dataRecv(createRequest(pres));
		expect(conn.roster.contacts).toEqual({'n@d/r': 'available'});
	});

	it("removes contacts on presence stanza where type is unavailable", function() {
		var pres = $pres({from: 'n@d/r', type: 'unavailable'});
		conn.roster.contacts = { 'n@d/r': 'available'};
		conn._dataRecv(createRequest(pres));
		expect(conn.roster.contacts).toEqual({});
	});

	it("calls callback on presence stanza", function() {
		spyOn(roster,'onPresenceChanged').andCallThrough();
		var pres = $pres({from: 'n@d/r'});
		conn._dataRecv(createRequest(pres));
		expect(conn.roster.contacts).toEqual({'n@d/r': 'available'});
		expect(roster.onPresenceChanged).toHaveBeenCalledWith('n@d/r','available');

		pres = $pres({from: 'n@d/r', type: 'unavailable'});
		conn._dataRecv(createRequest(pres));
		expect(conn.roster.contacts).toEqual({});
		expect(roster.onPresenceChanged).toHaveBeenCalledWith('n@d/r','unavailable');
	});
	describe("state and callback", function() {
		var c1, c2;
		beforeEach(function() {
			c1 = new Strophe.Connection();
			c2 = new Strophe.Connection();
			c1._changeConnectStatus(Strophe.Status.CONNECTED);
			c2._changeConnectStatus(Strophe.Status.CONNECTED);
			c1.authenticated = c2.authenticated = true;
		});

		it("allows per connection contacts", function() {
			var pres = $pres({from: 'n@d/r'});
			c1._dataRecv(createRequest(pres));
			expect(c2.roster.contacts).toEqual({});
			expect(c1.roster.contacts).toEqual({'n@d/r': 'available'});
		});
		it("allows per connection callbacks", function() {
			var pres = $pres({from: 'n@d/r'});
			var cb = jasmine.createSpy();
			spyOn(c1.roster,'onPresenceChanged').andCallThrough();
			c2.roster.onPresenceChanged = cb;
			c1._dataRecv(createRequest(pres));
			c2._dataRecv(createRequest(pres));
			expect(cb).toHaveBeenCalled();
			expect(cb.callCount).toEqual(1);
			expect(c1.roster.onPresenceChanged.callCount).toEqual(1);
		});
	});
});

