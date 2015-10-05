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

/**
 * XMPPHP BOSH
 *
 * @package   XMPPHP
 * @author    Nathanael C. Fritz <JID: fritzy@netflint.net>
 * @author    Stephan Wentz <JID: stephan@jabber.wentz.it>
 * @author    Michael Garvin <JID: gar@netflint.net>
 * @copyright 2008 Nathanael C. Fritz
 * @version   $Id$
 */
class XMPPHP_BOSH extends XMPPHP_XMPP {

  /**
   * @var integer
   */
  protected $rid;

  /**
   * @var string
   */
  protected $sid;

  /**
   * @var string
   */
  protected $http_server;

  /**
   * @var array
   */
  protected $http_buffer = array();

  /**
   * @var string
   */
  protected $session = false;

  /**
   * @var integer
   */
  protected $inactivity;

  /**
   * Connect
   *
   * @param $server
   * @param $wait
   * @param $session
   */
  public function connect($server = null, $wait = '1', $session = false) {

    if (is_null($server)) {

      // If we aren't given the server http url, try and guess it
      $port_string = ($this->port AND $this->port != 80) ? ':' . $this->port : '';
      $this->http_server = 'http://' . $this->host . $port_string . '/http-bind/';
    }
    else {
      $this->http_server = $server;
    }

    $this->use_encryption = false;
    $this->session        = $session;
    $this->rid            = 3001;
    $this->sid            = null;
    $this->inactivity     = 0;

    if ($session) {
      $this->loadSession();
    }

    if (!$this->sid) {

      $body = $this->__buildBody();
      $body->addAttribute('hold', '1');
      $body->addAttribute('to', $this->server);
      $body->addAttribute('route', 'xmpp:' . $this->host . ':' . $this->port);
      $body->addAttribute('secure', 'true');
      $body->addAttribute('xmpp:version', '1.0', 'urn:xmpp:xbosh');
      $body->addAttribute('wait', strval($wait));
      $body->addAttribute('ack', '1');
      $body->addAttribute('xmlns:xmpp', 'urn:xmpp:xbosh');
      $buff = '<stream:stream xmlns="jabber:client" xmlns:stream="http://etherx.jabber.org/streams">';
      xml_parse($this->parser, $buff, false);
      $response = $this->__sendBody($body);
      $rxml = new SimpleXMLElement($response);
      $this->sid = $rxml['sid'];
      $this->inactivity = $rxml['inactivity'];
    }
    else {
      $buff = '<stream:stream xmlns="jabber:client" xmlns:stream="http://etherx.jabber.org/streams">';
      xml_parse($this->parser, $buff, false);
    }
  }

  /**
   * Send body
   *
   * @param $body
   * @param $recv
   */
  public function __sendBody($body = null, $recv = true) {

    if (!$body) {
      $body = $this->__buildBody();
    }

    $output = '';
    $header = array('Accept-Encoding: gzip, deflate', 'Content-Type: text/xml; charset=utf-8');
    $ch     = curl_init();

    curl_setopt($ch, CURLOPT_URL, $this->http_server);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body->asXML());
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_VERBOSE, 0);

    if ($recv) {
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      $output = curl_exec($ch);
      if (curl_getinfo($ch, CURLINFO_HTTP_CODE) != '200') {
        throw new XMPPHP_Exception('Wrong response from server!');
      }

      $this->http_buffer[] = $output;
    }
    curl_close($ch);

    return $output;
  }

  /**
   * Build body
   *
   * @param $sub
   */
  public function __buildBody($sub = null) {

    $xml = '<body xmlns="http://jabber.org/protocol/httpbind" xmlns:xmpp="urn:xmpp:xbosh" />';
    $xml = new SimpleXMLElement($xml);
    $xml->addAttribute('content', 'text/xml; charset=utf-8');
    $xml->addAttribute('rid', $this->rid);
    $this->rid++;
    if ($this->sid) {
      $xml->addAttribute('sid', $this->sid);
    }

    $xml->addAttribute('xml:lang', 'en');

    if ($sub !== null) {

      // Ok, so simplexml is lame
      $parent  = dom_import_simplexml($xml);
      $content = dom_import_simplexml($sub);
      $child   = $parent->ownerDocument->importNode($content, true);
      $parent->appendChild($child);
      $xml     = simplexml_import_dom($parent);
    }

    return $xml;
  }

  /**
   * Process
   *
   * @param $null1
   * @param $null2
   *
   * null params are not used and just to statify Strict Function Declaration
   */
  public function __process($null1 = null, $null2 = null) {

    if ($this->http_buffer) {
      $this->__parseBuffer();
    }
    else {
      $this->__sendBody();
      $this->__parseBuffer();
    }

    $this->saveSession();

    return true;
  }

  public function __parseBuffer() {

    while ($this->http_buffer) {

      $idx    = key($this->http_buffer);
      $buffer = $this->http_buffer[$idx];
      unset($this->http_buffer[$idx]);

      if ($buffer) {

        $xml      = new SimpleXMLElement($buffer);
        $children = $xml->xpath('child::node()');

        foreach ($children as $child) {
          $buff = $child->asXML();
          $this->log->log('RECV: ' . $buff,  XMPPHP_Log::LEVEL_VERBOSE);
          xml_parse($this->parser, $buff, false);
        }
      }
    }
  }

  /**
   * Process
   *
   * @param $msg
   * @param $null
   *
   * null param are not used and just to statify Strict Function Declaration
   */
  public function send($msg, $null = null) {
    $this->log->log('SEND: ' . $msg,  XMPPHP_Log::LEVEL_VERBOSE);
    $msg = new SimpleXMLElement($msg);
    $this->__sendBody($this->__buildBody($msg), true);
  }

  /**
   * Reset
   *
   */
  public function reset() {

    $this->xml_depth = 0;
    unset($this->xmlobj);
    $this->xmlobj = array();
    $this->setupParser();
    $body = $this->__buildBody();
    $body->addAttribute('to', $this->host);
    $body->addAttribute('xmpp:restart', 'true', 'urn:xmpp:xbosh');
    $buff = '<stream:stream xmlns="jabber:client" xmlns:stream="http://etherx.jabber.org/streams">';
    $response = $this->__sendBody($body);
    $this->been_reset = true;
    xml_parse($this->parser, $buff, false);
  }

  /**
   * Load session
   *
   */
  public function loadSession() {

    if ($this->session == 'ON_FILE') {

      // Session not started so use session_file
      $session_file = $this->getSessionFile();

      // manage multiple accesses
      if (!file_exists($session_file)) {
        file_put_contents($session_file, '');
      }
      $session_file_fp    = fopen($session_file, 'r');
      flock($session_file_fp, LOCK_EX);
      $session_serialized = file_get_contents($session_file, null, null, 6);
      flock($session_file_fp, LOCK_UN);
      fclose($session_file_fp);

      $this->log->log('SESSION: reading ' . $session_serialized . ' from ' . $session_file,  XMPPHP_Log::LEVEL_VERBOSE);
      if ($session_serialized != '') {
        $_SESSION['XMPPHP_BOSH'] = unserialize($session_serialized);
      }
    }

    if (isset($_SESSION['XMPPHP_BOSH']['inactivity'])) {
      $this->inactivity = $_SESSION['XMPPHP_BOSH']['inactivity'];
    }

    $this->lat = (time() - (isset($_SESSION['XMPPHP_BOSH']['lat']))) ? $_SESSION['XMPPHP_BOSH']['lat'] : 0;

    if ($this->lat < $this->inactivity) {

      if (isset($_SESSION['XMPPHP_BOSH']['RID'])) {
        $this->rid = $_SESSION['XMPPHP_BOSH']['RID'];
      }
      if (isset($_SESSION['XMPPHP_BOSH']['SID'])) {
        $this->sid = $_SESSION['XMPPHP_BOSH']['SID'];
      }
      if (isset($_SESSION['XMPPHP_BOSH']['authed'])) {
        $this->authed = $_SESSION['XMPPHP_BOSH']['authed'];
      }
      if (isset($_SESSION['XMPPHP_BOSH']['basejid'])) {
        $this->basejid = $_SESSION['XMPPHP_BOSH']['basejid'];
      }
      if (isset($_SESSION['XMPPHP_BOSH']['fulljid'])) {
        $this->fulljid = $_SESSION['XMPPHP_BOSH']['fulljid'];
      }
    }
  }

  /**
   * Save session
   *
   */
  public function saveSession() {

    $_SESSION['XMPPHP_BOSH']['RID']        = (string) $this->rid;
    $_SESSION['XMPPHP_BOSH']['SID']        = (string) $this->sid;
    $_SESSION['XMPPHP_BOSH']['authed']     = (boolean) $this->authed;
    $_SESSION['XMPPHP_BOSH']['basejid']    = (string) $this->basejid;
    $_SESSION['XMPPHP_BOSH']['fulljid']    = (string) $this->fulljid;
    $_SESSION['XMPPHP_BOSH']['inactivity'] = (string) $this->inactivity;
    $_SESSION['XMPPHP_BOSH']['lat']        = (string) time();

    if ($this->session == 'ON_FILE') {

      $session_file    = $this->getSessionFile();
      $session_file_fp = fopen($session_file, 'r');
      flock($session_file_fp, LOCK_EX);
      // <?php prefix used to mask the content of the session file
      $session_serialized = '<?php ' . serialize($_SESSION);
      file_put_contents($session_file, $session_serialized);
      flock($session_file_fp, LOCK_UN);
      fclose($session_file_fp);
    }
  }

  /**
   * Disconnect
   *
   */
  public function disconnect(){

    parent::disconnect();

    if ($this->session == 'ON_FILE') {
      unlink($this->getSessionFile());
    }
    else {
      $keys = array('RID', 'SID', 'authed', 'basejid', 'fulljid', 'inactivity', 'lat');
      foreach ($keys as $key) {
        unset($_SESSION['XMPPHP_BOSH'][$key]);
      }
    }
  }

  /**
   * Get the session file
   *
   */
  public function getSessionFile() {
    return sys_get_temp_dir() . '/' . $this->user . '_' . $this->server . '_session';
  }
}
