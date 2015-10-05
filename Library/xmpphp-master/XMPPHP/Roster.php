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

/**
 * XMPPHP Roster
 *
 * @package   XMPPHP
 * @author    Nathanael C. Fritz <JID: fritzy@netflint.net>
 * @author    Stephan Wentz <JID: stephan@jabber.wentz.it>
 * @author    Michael Garvin <JID: gar@netflint.net>
 * @copyright 2008 Nathanael C. Fritz
 * @version   $Id$
 */
class XMPPHP_Roster {

  /**
   * Roster array, handles contacts and presence.  Indexed by jid.
   * Contains array with potentially two indexes 'contact' and 'presence'
   * @var array
   */
  protected $roster_array = array();

  /**
   * Constructor
   *
   */
  public function __construct($roster_array = array()) {
    // Allow for prepopulation with existing roster
    $this->roster_array = ($this->verifyRoster($roster_array)) ? $roster_array : array();
  }

  /**
   * Check that a given roster array is of a valid structure (empty is still valid)
   *
   * @param array $roster_array
   */
  protected function verifyRoster($roster_array) {
    // TODO once we know *what* a valid roster array looks like
    return true;
  }

  /**
   * Add given contact to roster
   *
   * @param string $jid
   * @param string $subscription
   * @param string $name
   * @param array  $groups
   */
  public function addContact($jid, $subscription, $name = '', $groups = array()) {

    $contact = array(
      'jid'          => $jid,
      'subscription' => $subscription,
      'name'         => $name,
      'groups'       => $groups,
    );

    if ($this->isContact($jid)) {
      $this->roster_array[$jid]['contact'] = $contact;
    }
    else {
      $this->roster_array[$jid] = array('contact' => $contact);
    }
  }

  /**
   * Retrieve contact via jid
   *
   * @param string $jid
   */
  public function getContact($jid) {
    if ($this->isContact($jid)) {
      return $this->roster_array[$jid]['contact'];
    }
  }

  /**
   * Discover if a contact exists in the roster via jid
   *
   * @param string $jid
   */
  public function isContact($jid) {
    return (array_key_exists($jid, $this->roster_array));
  }

  /**
   * Set presence
   *
   * @param string $presence
   * @param integer $priority
   * @param string $show
   * @param string $status
  */
  public function setPresence($presence, $priority, $show, $status) {

    $presence = explode('/', $presence, 2);
    $jid      = $presence[0];
    $resource = isset($presence[1]) ? $presence[1] : '';

    if ($show != 'unavailable') {

      if (!$this->isContact($jid)) {
        $this->addContact($jid, 'not-in-roster');
      }

      $array = array(
        'priority' => $priority,
        'show'     => $show,
        'status'   => $status,
      );

      $this->roster_array[$jid]['presence'][$resource] = $array;
    }
    else {
      // Nuke unavailable resources to save memory
      unset($this->roster_array[$jid]['resource'][$resource]);
      unset($this->roster_array[$jid]['presence'][$resource]);
    }
  }

  /**
   * Return best presence for jid
   *
   * @param string $jid
   */
  public function getPresence($jid) {

    $jid = array_shift(explode('/', $jid, 2));

    if ($this->isContact($jid)) {

      // Priorities can only be -128 = 127
      $current = array(
        'resource' => '',
        'active' => '',
        'priority' => -129,
        'show' => '',
        'status' => '',
      );

      foreach ($this->roster_array[$jid]['presence'] as $resource => $presence) {

        /**
         * Highest available priority or just highest priority
         *
         * NOTE: This part may be confused, I don't like more than 80~90 line length if's
         * What style do you like?
         */

        /** Style 1
        $condition1 = ($presence['priority'] > $current['priority']);
        $condition2 = ($presence['show'] == 'chat');
        $condition3 = ($presence['show'] == 'available');
        $condition4 = ($current['show'] != 'chat');
        $condition5 = ($current['show'] != 'available');
        if ($condition1 AND (($condition2 OR $condition3) OR ($condition4 OR $condition5))) { */

        /** Style 2
        $condition1 = ($presence['priority'] > $current['priority']);
        $condition2 = ($presence['show'] == 'chat' OR $presence['show'] == 'available');
        $condition3 = ($current['show'] != 'chat' OR $current['show'] != 'available');
        if ($condition1 AND ($condition2 OR $condition3)) { */

        // Style 3, same as 2 but using in_array() to avoid redundant $vars - my choice
        $in_array   = array('chat', 'available');
        $condition1 = ($presence['priority'] > $current['priority']);
        $condition2 = (in_array($presence['show'], $in_array));
        $condition3 = (!in_array($current['show'], $in_array));

        if ($condition1 AND ($condition2 OR $condition3)) {
          $current             = $presence;
          $current['resource'] = $resource;
        }
      }

      return $current;
    }
  }

  /**
   * Get roster
   *
   */
  public function getRoster() {
    return $this->roster_array;
  }
}
