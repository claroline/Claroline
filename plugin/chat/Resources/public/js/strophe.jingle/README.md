strophe.jingle
==============

strophe.jingle is a webrtc connection plugin for [strophe.js](http://strophe.im/strophejs/). Strophe is a popular library for writing XMPP client applications that run on any of the current popular browsers. Instead of the native TCP binding, strophe.js uses BOSH (Bidirectional-streams Over Synchronous HTTP, a variant of long polling) to connect to an XMPP server. Besides enabling anyone to build (federated) IM applications, this opens up the browser as an addressable endpoint for two-way exchange of structured messages, including presence and publish-subscribe applications.

This plugin makes it possible to negotiate audio/video streams via XMPP and then relinquish control to the WebRTC support of browsers like Firefox and Chrome for the actual out-of-band media streams. XMPP/Jingle you get the authenticated, secured and federated media signaling, whereas WebRTC gives you an API to set up the media streams using RTP/ICE/STUN and provide access to cameras and microphones.

The ["Silo-Free WebRTC"](http://vimeo.com/77289728) talk from the 2013 Realtime Conference explains this very well. The XMPP specific part starts around 17:00.

Features:
- mostly standards-compliant jingle, mapping from WebRTCs SDP to Jingle and vice versa. Aiming for full compliance with XEPs 0166, 0167, 0293, 0294 and 0320.
- tested with chrome and firefox.
- interoperable with [stanza.io](https://github.com/legastero/stanza.io).
- trickle and non-trickle modes for ICE (XEP-0176). Even supports early candidates from peer using PRANSWER.
- support for fetching time-limited STUN/TURN credentials through XEP-0215. [rfc5766-turn-server](https://code.google.com/p/rfc5766-turn-server/) is a TURN server which implements this method.
- comes with a sample demonstrating the use of this to build a federated multi-user conference (in full-mesh mode). When [hark](https://github.com/latentflip/hark) is available, the local audio volume is visualized (in Chrome M29+).
- the [jingle-interop-demos repository](https://github.com/legastero/jingle-interop-demos/tree/gh-pages/strophejingle) also contains a sample of 1-1 chat.

Events:
- callincoming.jingle (sid) -- you should accept the session here
- callterminated.jingle (sid)
- nostuncandidates.jingle (sid)
- remotestreamadded.jingle (event, sid)
- remotestreamremoved.jingle (event, sid)
- iceconnectionstatechange.jingle (sid, session)
- mediaready.jingle (stream)
- mediafailure.jingle
- ringing.jingle (sid)
- mute.jingle (sid, content)
- unmute.jingle (sid, content)
- ack.jingle (sid, ack)
- error.jingle (sid, error)
- packetloss.jingle (sid, loss) -- percentage of packets lost
