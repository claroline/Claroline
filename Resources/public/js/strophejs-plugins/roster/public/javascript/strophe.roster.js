(function(Strophe) {

	// only presence based, add roster later if needed, see chapter14
	function log(msg) {
		if (window.console) {
			console.log('roster.' + msg);
		}
	}

	var roster = {
		init: function (conn) {
			this._conn = conn;
			this.contacts = {};
			Strophe.addNamespace('ROSTER', 'jabber:iq:roster');
		},
		statusChanged: function (status) {
			if (status === Strophe.Status.CONNECTED) {
				this._conn.send($pres());
				this._conn.addHandler(this.presenceChanged.bind(this),
									  null, "presence");
			} 
		},
		presenceChanged: function(pres) {
			var from = pres.getAttribute('from');
			var type = pres.getAttribute('type') || "available";
			if (from !== this._conn.jid) {
				this.contacts[from] = type;
				if (type === "unavailable") {
					delete this.contacts[from];
				}
				this.onPresenceChanged(from,type);
			}
			return true;
		},
		onPresenceChanged: function(from,type) {
			log('onPresenceChanged: ' + from + " => " + type);
		}
	};
	Strophe.addConnectionPlugin('roster', roster);
})(Strophe);
