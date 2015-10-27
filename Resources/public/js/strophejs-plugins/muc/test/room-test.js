//var buster = require('buster');

var id = 0;

(function() {
const gate = "https://conversejs.org/http-bind";
const user = "dima@tigase.im";
const password = "master";

function RoomClient(name) {
  var messages = [];
  var presences = [];
  var wrongHandler = false;

  this.message = when.defer();
  this.presence = when.defer();
  this.fail = when.defer();

  this.messages = function() { return messages.slice(); }
  this.presences = function() { return presences.slice(); }
  this.wrongHandler = function() { return wrongHandler; }
  this.name = function() { return name; }

  this.onMessage = function(stanza, room) {
    if(room.name != name) {
      wrongHandler = true;
      this.fail.resolver.resolve();
      this.fail = when.defer();
      return false;
    }

    messages.push(stanza);
    this.message.resolver.resolve();
    this.message = when.defer();
    return true;
  }.bind(this);

  this.onPresence = function(stanza, room) {
    if(room.name != name) {
      wrongHandler = true;
      this.fail.resolver.resolve();
      this.fail = when.defer();
      return false;
    }

    presences.push(stanza);
    this.presence.resolver.resolve();
    this.presence = when.defer();
    return true;
  }.bind(this);
};

buster.testCase("Check room handlers", {
  setUp: function() {
    this.timeout = 20000;

    this.connection = new ConnectionSentinel();
    var pr = this.connection.connect(gate, user, password);
    this.plugin = this.connection._connection.muc;

    return pr;
  },

  tearDown: function() {
    if(!this.connection._connected) return;
    return this.connection.disconnect();
  },

  "Rooms should have separate callbacks": function() {
    var sentinel = when.defer();

    var rooms = [new RoomClient("room-1@muc.tigase.im"),
                 new RoomClient("room-2@muc.tigase.im")];

    var gotPresence = false;
    var gotMessage = false;
    this.plugin.join(rooms[0].name(), "dima", rooms[0].onMessage, rooms[0].onPresence);
    rooms[0].presence.promise.then(function() {
      gotPresence = true;
      this.plugin.groupchat(rooms[0].name(), "Hello, world!");
      return true;
    }.bind(this));
    rooms[0].message.promise.then(function() {
      gotMessage = true;
      return true;
    });
    this.plugin.join(rooms[1].name(), "dima", rooms[1].onMessage, rooms[1].onPresence);

    setTimeout(function() {
      assert(gotPresence);
      assert(gotMessage);
      refute(rooms[0].wrongHandler());
      refute(rooms[1].wrongHandler());

      sentinel.resolver.resolve();
    }, 2000);

    return sentinel.promise;
  },

  "Callback should not be removed when we leave one room": function() {
    var sentinel = when.defer();

    var rooms = [new RoomClient("room-6@muc.tigase.im"),
                 new RoomClient("room-7@muc.tigase.im")];

    var gotMessage = false;
    var messageWasSent = false;
    this.plugin.join(rooms[0].name(), "dima", rooms[0].onMessage, rooms[0].onPresence);
    rooms[0].presence.promise.then(function() {
      this.plugin.join(rooms[1].name(), "dima", rooms[1].onMessage, rooms[1].onPresence);
      rooms[1].presence.promise.then(function() {
        this.plugin.leave(rooms[0].name(), "dima");
        setTimeout(function() {
          messageWasSent = true;
          this.plugin.groupchat(rooms[1].name(), "Hello, world!");
          rooms[1].message.promise.then(function() {

            gotMessage = true;
            return true;
          }.bind(this));
        }.bind(this), 1000);
        return true;
      }.bind(this));
      return true;
    }.bind(this));

    setTimeout(function() {
      assert(messageWasSent, "Message has to be sent, i.e. all the room join/leave worked");
      assert(gotMessage, "Message has to be received.");
      sentinel.resolver.resolve();
    }.bind(this), 6000);
    return sentinel.promise;
  }
});
})();
