<?php

/**
 * XMPPHP: The PHP XMPP Library
 * Copyright (C) 2008  Nathanael C. Fritz
 * This file is part of SleekXMPP.
 *
 * XMPPHP is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * XMPPHP is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with XMPPHP; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category  xmpphp
 * @package   XMPPHP
 * @author    Nathanael C. Fritz <JID: fritzy@netflint.net>
 * @author    Stephan Wentz <JID: stephan@jabber.wentz.it>
 * @author    Michael Garvin <JID: gar@netflint.net>
 * @copyright 2008 Nathanael C. Fritz
 */

/** XMPPHP_XMLStream */
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'XMLStream.php';

/** XMPPHP_Roster */
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Roster.php';

/**
 * XMPPHP XMPP
 *
 * @package   XMPPHP
 * @author    Nathanael C. Fritz <JID: fritzy@netflint.net>
 * @author    Stephan Wentz <JID: stephan@jabber.wentz.it>
 * @author    Michael Garvin <JID: gar@netflint.net>
 * @copyright 2008 Nathanael C. Fritz
 * @version   $Id$
 */
class XMPPHP_XMPP extends XMPPHP_XMLStream {

  /**
   * @var string
   */
  public $server;

  /**
   * @var string
   */
  public $user;

  /**
   * @var string
   */
  protected $password;

  /**
   * @var string
   */
  protected $resource;

  /**
   * @var string
   */
  protected $fulljid;

  /**
   * @var string
   */
  protected $basejid;

  /**
   * @var boolean
   */
  protected $authed = false;

  /**
   * @var boolean
   */
  protected $session_started = false;

  /**
   * @var boolean
   */
  protected $auto_subscribe = false;

  /**
   * @var boolean
   */
  protected $use_encryption = true;

  /**
   * @var boolean
   */
  public $track_presence = true;

  /**
   * @var object
   */
  public $roster;

  /**
   * @var array supported auth mechanisms
   */
  protected $auth_mechanism_supported = array('PLAIN', 'DIGEST-MD5');

  /**
   * @var string default auth mechanism
   */
  protected $auth_mechanism_default = 'PLAIN';

  /**
   * @var string prefered auth mechanism
   */
  protected $auth_mechanism_preferred = 'DIGEST-MD5';

  /**
   * Constructor
   *
   * @param string  $host
   * @param integer $port
   * @param string  $user
   * @param string  $password
   * @param string  $resource
   * @param string  $server
   * @param boolean $printlog
   * @param string  $loglevel
   */
  public function __construct($host, $port, $user, $password, $resource, $server = null, $printlog = false, $loglevel = null) {

    parent::__construct($host, $port, $printlog, $loglevel);

    if (!$server) {
      $server = $host;
    }

    $this->user           = $user;
    $this->password       = $password;
    $this->resource       = $resource;
    $this->server         = $server;
    $this->basejid        = $this->user . '@' . $this->server;
    $this->roster         = new XMPPHP_Roster();
    $this->track_presence = true;
    $this->stream_start   = '<stream:stream to="' . $server . '" xmlns:stream="http://etherx.jabber.org/streams" xmlns="jabber:client" version="1.0">';
    $this->stream_end     = '</stream:stream>';
    $this->default_ns     = 'jabber:client';

    $this->addXPathHandler('{http://etherx.jabber.org/streams}features', 'features_handler');
    $this->addXPathHandler('{urn:ietf:params:xml:ns:xmpp-sasl}success', 'sasl_success_handler');
    $this->addXPathHandler('{urn:ietf:params:xml:ns:xmpp-sasl}failure', 'sasl_failure_handler');
    $this->addXPathHandler('{urn:ietf:params:xml:ns:xmpp-tls}proceed', 'tls_proceed_handler');
    $this->addXPathHandler('{jabber:client}message', 'message_handler');
    $this->addXPathHandler('{jabber:client}presence', 'presence_handler');

    // For DIGEST-MD5 auth:
    $this->addXPathHandler('{urn:ietf:params:xml:ns:xmpp-sasl}challenge', 'sasl_challenge_handler');
  }

  /**
   * Turn encryption on/off
   *
   * @param boolean $useEncryption
   */
  public function useEncryption($useEncryption = true) {
    $this->use_encryption = $useEncryption;
  }

  /**
   * Turn on auto-authorization of subscription requests.
   *
   * @param boolean $autoSubscribe
   */
  public function autoSubscribe($autoSubscribe = true) {
    $this->auto_subscribe = $autoSubscribe;
  }

  /**
   * Send XMPP Message
   *
   * @param string $to
   * @param string $body
   * @param string $type
   * @param string $subject
   */
  public function message($to, $body, $type = 'chat', $subject = null, $payload = null) {

    if (is_null($type)) {
      $type = 'chat';
    }

    $to      = htmlspecialchars($to);
    $body    = htmlspecialchars($body);
    $subject = htmlspecialchars($subject);
    $subject = ($subject) ? '<subject>' . $subject . '</subject>' : '';
    $payload = ($payload) ? $payload : '';
    $sprintf = '<message from="%s" to="%s" type="%s">%s<body>%s</body>%s</message>';
    $output  = sprintf($sprintf, $this->fulljid, $to, $type, $subject, $body, $payload);

    $this->send($output);
  }

  /**
   * Set Presence
   *
   * @param string $status
   * @param string $show
   * @param string $to
   */
  public function presence($status = null, $show = 'available', $to = null, $type = 'available', $priority = null, $payload = null) {

    $to       = htmlspecialchars($to);
    $status   = htmlspecialchars($status);
    $type     = ($type != 'available') ? $type : '';
    $type     = ($show != 'unavailable') ? $type : $show;
    $show     = ($show != 'available' AND $show != null) ? '<show>' . $show . '</show>' : '';
    $status   = ($status != null) ? '<status>' . $status . '</status>' : '';
    $priority = ($priority !== null) ? '<priority>' . $priority . '</priority>' : '';
    $payload  = ($payload) ? $payload : '';
    $to       = ($to) ? 'to="' . $to . '"' : '';
    $type     = ($type) ? 'type="' . $type . '"' : '';

    if ($show == 'available' AND $status == null AND $priority == null AND $payload == null) {
      $sprintf = '<presence %s %s />';
      $output  = sprintf($sprintf, $to, $type);
    }
    else {
      $sprintf = '<presence %s %s>%s%s%s%s</presence>';
      $output  = sprintf($sprintf, $to, $type, $show, $status, $priority, $payload);
    }

    $this->send($output);
  }

  /**
   * Send Auth request
   *
   * @param string $jid
   */
  public function subscribe($jid) {
    $sprintf  = '<presence type="subscribe" to="%s" from="%s" />';
    $presence = sprintf($sprintf, $jid, $this->fulljid);
    $this->send($presence);
  }

  /**
   * Add user to Roster
   *
   * @param string $jid user jid
   * @param string $name user nickname
   * @param string $group group to add
   */
  public function RosterAddUser($jid, $name = null, $group = null) {

    $name    = htmlspecialchars($name);
    $group   = htmlspecialchars($group, ENT_QUOTES, 'UTF-8');
    $name    = ($name) ? 'name="' . $name . '"': '';
    $group   = ($group) ? '<group>' . $group . '</group>' : '';
    $sprintf = '<item jid="%s" %s />%s';
    $payload = sprintf($sprintf, $jid, $name, $group);
    $this->sendIq(null, 'set', 'jabber:iq:roster', $payload);
  }

  /**
   * Send ID action
   *
   * @param string $to to jid
   * @param string $type type of ID
   * @param string $xmlns xmlns name
   * @param string $payload payload string
   * @param string $from from jid
   */
  private function sendIq($to = null, $type = 'get', $xmlns = null, $payload = null, $from = null, $id = null) {

    if ($id == null) {
      $id = $this->getID();
    }

    $to      = htmlspecialchars($to);
    $to      = ($to) ? 'to="' . $to . '"' : '';
    $from    = ($from) ? 'from="' . $from . '"' : '';
    $sprintf = '<iq type="%s" id="%s" %s %s><query xmlns="%s">%s</query></iq>';
    $xml     = sprintf($sprintf, $type, $id, $to, $from, $xmlns, $payload);

    return $this->send($xml);
  }

  /**
   * Message handler
   *
   * @param string $xml
   */
  public function message_handler($xml) {

    $payload = array();

    $body            = $xml->sub('body');
    $payload['xml']  = $xml;
    $payload['type'] = (isset($xml->attrs['type'])) ? $xml->attrs['type'] : 'chat';
    $payload['from'] = $xml->attrs['from'];
    $payload['body'] = is_object($body) ? $body->data : false;
    $this->log->log('Message: ' . $payload['body'], XMPPHP_Log::LEVEL_DEBUG);
    $this->event('message', $payload);
  }

  /**
   * Presence handler
   *
   * @param string $xml
   */
  public function presence_handler($xml) {

    $payload = array();

    $payload['from'] = $xml->attrs['from'];

    if ($xml->hasSub('x')) {

      $x = $xml->sub('x');

      if ($x->hasSub('status')) {

        switch ($x->sub('status')->attrs['code']) {

          case '201':
            $id    = $this->getId();
            $array = array('xmlns' => 'jabber:x:data', 'type' => 'submit');
            $this->addIdHandler($id, 'room_join_handler');
            $this->sendIq($payload['from'], 'set', 'http://jabber.org/protocol/muc#owner', $this->x($array), null, $id);
            $this->log->log('Presence: sending default config for created room...',  XMPPHP_Log::LEVEL_DEBUG);
            break;

          case '110':
            $payload['affiliation'] = $x->sub('item')->attrs['affiliation'];
            $payload['role']        = $x->sub('item')->attrs['role'];
            $this->event('room_joined');
            break;
        }
      }
    }

    if ($xml->hasSub('error')) {
      // TODO: $error->attrs['type']; different types of error may need different management
      $error = $xml->sub('error');
      $this->event('presence_error', $payload);
    }

    $payload['type']     = (isset($xml->attrs['type'])) ? $xml->attrs['type'] : 'available';
    $payload['show']     = (isset($xml->sub('show')->data)) ? $xml->sub('show')->data : $payload['type'];
    $payload['status']   = (isset($xml->sub('status')->data)) ? $xml->sub('status')->data : '';
    $payload['priority'] = (isset($xml->sub('priority')->data)) ? intval($xml->sub('priority')->data) : 0;
    $payload['xml']      = $xml;

    if ($this->track_presence) {
      $this->roster->setPresence($payload['from'], $payload['priority'], $payload['show'], $payload['status']);
    }

    $this->log->log('Presence: ' . $payload['from'] . ' [' . $payload['show'] . '] ' . $payload['status'],  XMPPHP_Log::LEVEL_DEBUG);

    if (array_key_exists('type', $xml->attrs) AND $xml->attrs['type'] == 'subscribe') {

      if ($this->auto_subscribe) {
        $sprintf = '<presence type="%s" to="%s" from="%s" />';
        $this->send(sprintf($sprintf, 'subscribed', $xml->attrs['from'], $this->fulljid));
        $this->send(sprintf($sprintf, 'subscribe', $xml->attrs['from'], $this->fulljid));
      }

      $this->event('subscription_requested', $payload);
    }
    elseif (array_key_exists('type', $xml->attrs) AND $xml->attrs['type'] == 'subscribed') {
      $this->event('subscription_accepted', $payload);
    }
    else {
      $this->event('presence', $payload);
    }
  }

  /**
   * Features handler
   *
   * @param string $xml
   */
  protected function features_handler($xml) {

    if ($xml->hasSub('starttls') AND $this->use_encryption) {
      $this->send('<starttls xmlns="urn:ietf:params:xml:ns:xmpp-tls"><required /></starttls>');
    }
    elseif ($xml->hasSub('bind') AND $this->authed) {

      $id      = $this->getId();
      $this->addIdHandler($id, 'resource_bind_handler');
      $bind    = '<bind xmlns="urn:ietf:params:xml:ns:xmpp-bind"><resource>' . $this->resource . '</resource></bind>';
      $sprintf = '<iq xmlns="jabber:client" type="set" id="%s">%s</iq>';
      $this->send(sprintf($sprintf, $id, $bind));
    }
    else {

      $this->log->log('Attempting Auth...');

      if ($this->password) {

        $mechanism = 'PLAIN'; // default

        if ($xml->hasSub('mechanisms') AND $xml->sub('mechanisms')->hasSub('mechanism')) {

          // Get the list of all available auth mechanism that we can use
          $available = array();

          foreach ($xml->sub('mechanisms')->subs as $sub) {

            if ($sub->name == 'mechanism') {
              if (in_array($sub->data, $this->auth_mechanism_supported)) {
                $available[$sub->data] = $sub->data;
              }
            }
          }

          if (isset($available[$this->auth_mechanism_preferred])) {
            $mechanism = $this->auth_mechanism_preferred;
          }
          else {
            // Use the first available
            $mechanism = reset($available);
          }

          $this->log->log('Trying ' . $mechanism . ' (available: ' . implode(', ', $available) . ')');
        }

        switch ($mechanism) {

          case 'PLAIN':
            $password = base64_encode("\x00" . $this->user . "\x00" . $this->password);
            $this->send('<auth xmlns="urn:ietf:params:xml:ns:xmpp-sasl" mechanism="PLAIN">' . $password . '</auth>');
            break;

          case 'DIGEST-MD5':
            $this->send('<auth xmlns="urn:ietf:params:xml:ns:xmpp-sasl" mechanism="DIGEST-MD5" />');
            break;
        }
      }
      else {
        $this->send('<auth xmlns="urn:ietf:params:xml:ns:xmpp-sasl" mechanism="ANONYMOUS" />');
      }
    }
  }

  /**
   * SASL success handler
   *
   * @param string $xml
   */
  protected function sasl_success_handler($xml) {
    $this->log->log('Auth success!');
    $this->authed = true;
    $this->reset();
  }

  /**
   * SASL feature handler
   *
   * @param string $xml
   */
  protected function sasl_failure_handler($xml) {
    $this->log->log('Auth failed!',  XMPPHP_Log::LEVEL_ERROR);
    $this->disconnect();
    throw new XMPPHP_Exception('Auth failed!');
  }

  /**
   * Handle challenges for DIGEST-MD5 auth
   *
   * @param string $xml
   */
  protected function sasl_challenge_handler($xml) {

    // Decode and parse the challenge string
    // (may be something like foo="bar", foo2="bar2,bar3,bar4", foo3=bar5)
    $challenge = base64_decode($xml->data);
    $vars      = array();
    $matches   = array();
    $res       = array();
    preg_match_all('/(\w+)=(?:"([^"]*)|([^,]*))/', $challenge, $matches);

    foreach ($matches[1] as $k => $v) {
      $vars[$v] = (empty($matches[2][$k])) ? $matches[3][$k] : $matches[2][$k];
    }

    if (isset($vars['nonce'])) {

      // First step
      $vars['cnonce'] = uniqid(mt_rand(), false);
      $vars['nc']     = '00000001';
      $vars['qop']    = 'auth'; // Force qop to auth

      if (!isset($vars['digest-uri'])) {
        $vars['digest-uri'] = 'xmpp/' . $this->server;
      }
      if (!isset($vars['realm'])) {
        $vars['realm'] = '';
      }

      // Now, the magic... when the dreams come true!
      $auth1 = sprintf('%s:%s:%s', $this->user, $vars['realm'], $this->password);
      if ($vars['algorithm'] == 'md5-sess') {
        $auth1 = sprintf('%s:%s:%s', pack('H32', md5($auth1)), $vars['nonce'], $vars['cnonce']);
      }

      $auth2    = 'AUTHENTICATE:' . $vars['digest-uri'];
      $password = md5(sprintf('%s:%s:%s:%s:%s:%s', md5($auth1), $vars['nonce'], $vars['nc'], $vars['cnonce'], $vars['qop'], md5($auth2)));
      $sprintf  = 'username="%s",realm="%s",nonce="%s",cnonce="%s",nc="%s",qop="%s",digest-uri="%s",response="%s",charset="utf-8"';
      $response = sprintf($sprintf, $this->user, $vars['realm'], $vars['nonce'], $vars['cnonce'], $vars['nc'], $vars['qop'], $vars['digest-uri'], $password);
      $response = base64_encode($response);

      // Send the response
      $this->send('<response xmlns="urn:ietf:params:xml:ns:xmpp-sasl">' . $response . '</response>');
    }
    else {

      if (isset($vars['rspauth'])) {
        // Second step
        $this->send('<response xmlns="urn:ietf:params:xml:ns:xmpp-sasl" />');
      }
      else {
        $this->log->log('ERROR receiving challenge : ' . $challenge, XMPPHP_Log::LEVEL_ERROR);
      }
    }
  }

  /**
   * Resource bind handler
   *
   * @param string $xml
   */
  protected function resource_bind_handler($xml) {

    if ($xml->attrs['type'] == 'result') {
      $this->log->log('Bound to ' . $xml->sub('bind')->sub('jid')->data);
      $this->fulljid = $xml->sub('bind')->sub('jid')->data;
	  $exploded = explode('/', $this->fulljid);
      $this->jid = array_shift($exploded);
    }

    $id      = $this->getId();
    $session = '<session xmlns="urn:ietf:params:xml:ns:xmpp-session" />';
    $this->addIdHandler($id, 'session_start_handler');
    $this->send('<iq xmlns="jabber:client" type="set" id="' . $id . '">' . $session . '</iq>');
  }

  /**
   * Retrieves the roster
   *
   */
  public function getRoster() {

    $id    = $this->getID();
    $query = '<query xmlns="jabber:iq:roster" />';
    $this->addIdHandler($id, 'roster_iq_handler');
    $this->send('<iq xmlns="jabber:client" type="get" id="' . $id . '">' . $query . '</iq>');
  }

  /**
   * Roster iq handler
   * Gets all packets matching XPath "iq/{jabber:iq:roster}query'
   *
   * Implements RFC3921, 7.4. "Adding a Roster Item"
   *
   * @param string $xml
   */
  protected function roster_iq_handler($xml) {

    $status    = 'result';
    $xmlroster = $xml->sub('query');

    foreach ($xmlroster->subs as $item) {

      $groups = array();

      if ($item->name == 'item') {

        $jid = $item->attrs['jid']; // Required

        if (isset($item->attrs['name']) AND !empty($item->attrs['name'])) {
          $name = $item->attrs['name']; // May
        }
        else {
          $name = '';
        }

        $subscription = $item->attrs['subscription'];

        foreach ($item->subs as $subitem) {
          if ($subitem->name == 'group') {
            $groups[] = $subitem->data;
          }
        }

        //Store for action if no errors happen
        $contacts[] = array($jid, $subscription, $name, $groups);
      }
      else {
        $status = 'error';
      }
    }

    if ($status == 'result') { // No errors, add contacts
      foreach ($contacts as $contact) {
        $this->roster->addContact($contact[0], $contact[1], $contact[2], $contact[3]);
      }
    }

    if ($xml->attrs['type'] == 'set') {
      $sprintf = '<iq type="result" id="%s" to="%s" />';
      $this->send(sprintf($sprintf, $xml->attrs['id'], $xml->attrs['from']));
    }

    $this->event('roster_received');
  }

  /**
   * Session start handler
   *
   * @param string $xml
   */
  protected function session_start_handler($xml) {
    $this->log->log('Session started');
    $this->session_started = true;
    $this->event('session_start');
  }

  /**
   * TLS proceed handler
   *
   * @param string $xml
   */
  protected function tls_proceed_handler($xml) {
    $this->log->log('Starting TLS encryption');
    stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_SSLv23_CLIENT);
    $this->reset();
  }

  /**
   * Retrieves the vcard
   *
   */
  public function getVCard($jid = null) {

    $id      = $this->getID();
    $this->addIdHandler($id, 'vcard_get_handler');
    $jid     = ($jid) ? 'to="' . $jid . '"' : '';
    $sprintf = '<iq type="get" id="%s" %s><vCard xmlns="vcard-temp" /></iq>';
    $this->send(sprintf($sprintf, $id, $jid));
  }

  /**
   * VCard retrieval handler
   *
   * @param XML Object $xml
   */
  protected function vcard_get_handler($xml) {

    $vcard_array = array();
    $vcard       = $xml->sub('vcard');

    // Go through all of the sub elements and add them to the vcard array
    foreach ($vcard->subs as $sub) {

      if ($sub->subs) {

        $vcard_array[$sub->name] = array();

        foreach ($sub->subs as $sub_child) {
          $vcard_array[$sub->name][$sub_child->name] = $sub_child->data;
        }
      }
      else {
        $vcard_array[$sub->name] = $sub->data;
      }
    }
    $vcard_array['from'] = $xml->attrs['from'];
    $this->event('vcard', $vcard_array);
  }

  /**
   * Create xml for x tag
   *
   * @param string $attribs namespace or an associative array of attributes
   * @param string $payload
   */
  protected function x($attribs = null, $payload = null) {

    $output = '<x';

    if ($attribs != null) {

      if (is_array($attribs) === false) {
        $attribs = array('xmlns' => $attribs);
      }
      foreach ($attribs as $attrib => $value) {
        $output .= sprintf(' %s="%s"', $attrib, $value);
      }
    }

    $output .= ($payload != null) ? '>' . $payload . '</x>' : ' />';

    return $output;
  }

  protected function room_join_handler($xml) {
    $this->event('room_joined');
  }

  /**
   * Join a room for multi-chat (or create one if not exists)
   *
   * @param string $name room name
   * @param string $service hostname of the chat service
   * @param string $password
   */
  public function joinRoom($room = null, $service = null, $password = null) {

    if ($password != null) {
      $password = '<password>' . $password . '</password>';
    }

    $room_service      = $room . '@' . $service . '/' . $this->user;
    $protocol_password = $this->x('http://jabber.org/protocol/muc', $password);
    $this->presence(null, null, $room_service, null, null, $protocol_password);
  }
}
