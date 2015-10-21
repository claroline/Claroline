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
 * @category   xmpphp 
 * @package	XMPPHP
 * @author	 Nathanael C. Fritz <JID: fritzy@netflint.net>
 * @author	 Stephan Wentz <JID: stephan@jabber.wentz.it>
 * @author	 Michael Garvin <JID: gar@netflint.net>
 * @copyright  2008 Nathanael C. Fritz
 */

/** XMPPHP_XMLStream */
require_once dirname(__FILE__) . "/XMLStream.php";
require_once dirname(__FILE__) . "/Roster.php";

/**
 * XMPPHP Main Class
 * 
 * @category   xmpphp 
 * @package	XMPPHP
 * @author	 Nathanael C. Fritz <JID: fritzy@netflint.net>
 * @author	 Stephan Wentz <JID: stephan@jabber.wentz.it>
 * @author	 Michael Garvin <JID: gar@netflint.net>
 * @copyright  2008 Nathanael C. Fritz
 * @version	$Id$
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

	protected $user_name='';
	
    //用户的昵称，一般为真实姓名
    protected $nickName="";
    
    protected $from="";
    protected $to="";
    
    /**
     * 房间的配置
     * @var unknown_type
     */
    protected $room_setting=array();
    
    /**
     * 房间的配置的xml
     * @var unknown_type
     */
    protected $room_xml="";
    
    protected $ref_obj;

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
		
		$this->user	 = $user;
		$this->password = $password;
		$this->resource = $resource;
		if(!$server) $server = $host;
		$this->basejid = $this->user . '@' . $this->host;

		$this->roster = new Roster();
		$this->track_presence = true;

		$this->stream_start = '<stream:stream to="' . $server . '" xmlns:stream="http://etherx.jabber.org/streams" xmlns="jabber:client" version="1.0">';
		$this->stream_end   = '</stream:stream>';
		$this->default_ns   = 'jabber:client';
		
		$this->addXPathHandler('{http://etherx.jabber.org/streams}features', 'features_handler');
		$this->addXPathHandler('{urn:ietf:params:xml:ns:xmpp-sasl}success', 'sasl_success_handler');
		$this->addXPathHandler('{urn:ietf:params:xml:ns:xmpp-sasl}failure', 'sasl_failure_handler');
		$this->addXPathHandler('{urn:ietf:params:xml:ns:xmpp-tls}proceed', 'tls_proceed_handler');
		$this->addXPathHandler('{jabber:client}message', 'message_handler');
		$this->addXPathHandler('{jabber:client}presence', 'presence_handler');
		$this->addXPathHandler('iq/{jabber:iq:roster}query', 'roster_iq_handler');
	}

	/**
	 * Turn encryption on/ff
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
	    if(is_null($type))
	    {
	        $type = 'chat';
	    }
	    
		$to	  = htmlspecialchars($to);
		$body	= htmlspecialchars($body);
		$subject = htmlspecialchars($subject);
		
		$out = "<message from=\"{$this->fulljid}\" to=\"$to\" type='$type'>";
		if($subject) $out .= "<subject>$subject</subject>";
		$out .= "<body>$body</body>";
		if($payload) $out .= $payload;
		$out .= "</message>";
		
		$this->send($out);
	}
	


	/**
	 * Set Presence
	 *
	 * @param string $status
	 * @param string $show
	 * @param string $to
	 */
	public function presence($status = null, $show = 'available', $to = null, $type='available', $priority=0) {
		if($type == 'available') $type = '';
		$to	 = htmlspecialchars($to);
		$status = htmlspecialchars($status);
		if($show == 'unavailable') $type = 'unavailable';
		
		$out = "<presence";
		if($to) $out .= " to=\"$to\"";
		if($type) $out .= " type='$type'";
		if($show == 'available' and !$status) {
			$out .= "/>";
		} else {
			$out .= ">";
			if($show != 'available') $out .= "<show>$show</show>";
			if($status) $out .= "<status>$status</status>";
			if($priority) $out .= "<priority>$priority</priority>";
			$out .= "</presence>";
		}
		
		$this->send($out);
	}
	/**
	 * Send Auth request
	 *
	 * @param string $jid
	 */
	public function subscribe($jid) {
		$this->send("<presence type='subscribe' to='{$jid}' from='{$this->fulljid}' />");
		#$this->send("<presence type='subscribed' to='{$jid}' from='{$this->fulljid}' />");
	}

	/**
	 * Message handler
	 *
	 * @param string $xml
	 */
	public function message_handler($xml) {
		if(isset($xml->attrs['type'])) {
			$payload['type'] = $xml->attrs['type'];
		} else {
			$payload['type'] = 'chat';
		}
		$payload['from'] = $xml->attrs['from'];
		$payload['body'] = $xml->sub('body')->data;
		$payload['xml'] = $xml;
		$this->log->log("Message: {$xml->sub('body')->data}", XMPPHP_Log::LEVEL_DEBUG);
		$this->event('message', $payload);
	}

	/**
	 * Presence handler
	 *
	 * @param string $xml
	 */
	public function presence_handler($xml) {
		$payload['type'] = (isset($xml->attrs['type'])) ? $xml->attrs['type'] : 'available';
		$payload['show'] = (isset($xml->sub('show')->data)) ? $xml->sub('show')->data : $payload['type'];
		$payload['from'] = $xml->attrs['from'];
		$payload['status'] = (isset($xml->sub('status')->data)) ? $xml->sub('status')->data : '';
		$payload['priority'] = (isset($xml->sub('priority')->data)) ? intval($xml->sub('priority')->data) : 0;
		$payload['xml'] = $xml;
		if($this->track_presence) {
			$this->roster->setPresence($payload['from'], $payload['priority'], $payload['show'], $payload['status']);
		}
		$this->log->log("Presence: {$payload['from']} [{$payload['show']}] {$payload['status']}",  XMPPHP_Log::LEVEL_DEBUG);
		if(array_key_exists('type', $xml->attrs) and $xml->attrs['type'] == 'subscribe') {
			if($this->auto_subscribe) {
				$this->send("<presence type='subscribed' to='{$xml->attrs['from']}' from='{$this->fulljid}' />");
				$this->send("<presence type='subscribe' to='{$xml->attrs['from']}' from='{$this->fulljid}' />");
			}
			$this->event('subscription_requested', $payload);
		} elseif(array_key_exists('type', $xml->attrs) and $xml->attrs['type'] == 'subscribed') {
			$this->event('subscription_accepted', $payload);
		} else {
			$this->event('presence', $payload);
		}
	}

	/**
	 * Features handler
	 *
	 * @param string $xml
	 */
	protected function features_handler($xml) {
		if($xml->hasSub('starttls') and $this->use_encryption) {
			$this->send("<starttls xmlns='urn:ietf:params:xml:ns:xmpp-tls'><required /></starttls>");
		} elseif($xml->hasSub('bind') and $this->authed) {
			$id = $this->getId();
			$this->addIdHandler($id, 'resource_bind_handler');
			$this->send("<iq xmlns=\"jabber:client\" type=\"set\" id=\"$id\"><bind xmlns=\"urn:ietf:params:xml:ns:xmpp-bind\"><resource>{$this->resource}</resource></bind></iq>");
		} else {
			$this->log->log("Attempting Auth...");
			if ($this->password) {
			$this->send("<auth xmlns='urn:ietf:params:xml:ns:xmpp-sasl' mechanism='PLAIN'>" . base64_encode("\x00" . $this->user . "\x00" . $this->password) . "</auth>");
			} else {
                        $this->send("<auth xmlns='urn:ietf:params:xml:ns:xmpp-sasl' mechanism='ANONYMOUS'/>");
			}	
		}
	}

	/**
	 * SASL success handler
	 *
	 * @param string $xml
	 */
	protected function sasl_success_handler($xml) {
		$this->log->log("Auth success!");
		$this->authed = true;
		$this->reset();
	}
	
	/**
	 * SASL feature handler
	 *
	 * @param string $xml
	 */
	protected function sasl_failure_handler($xml) {
		$this->log->log("Auth failed!",  XMPPHP_Log::LEVEL_ERROR);
		$this->disconnect();
		
		throw new XMPPHP_Exception('Auth failed!');
	}

	/**
	 * Resource bind handler
	 *
	 * @param string $xml
	 */
	protected function resource_bind_handler($xml) {
		if($xml->attrs['type'] == 'result') {
			$this->log->log("Bound to " . $xml->sub('bind')->sub('jid')->data);
			$this->fulljid = $xml->sub('bind')->sub('jid')->data;
			$jidarray = explode('/',$this->fulljid);
			$this->jid = $jidarray[0];
		}
		$id = $this->getId();
		$this->addIdHandler($id, 'session_start_handler');
		$this->send("<iq xmlns='jabber:client' type='set' id='$id'><session xmlns='urn:ietf:params:xml:ns:xmpp-session' /></iq>");
	}

	/**
	* Retrieves the roster
	*
	*/
	public function getRoster() {
		$id = $this->getID();
		$this->send("<iq xmlns='jabber:client' type='get' id='$id'><query xmlns='jabber:iq:roster' /></iq>");
	}

	/**
	* Roster iq handler
	* Gets all packets matching XPath "iq/{jabber:iq:roster}query'
	*
	* @param string $xml
	*/
	protected function roster_iq_handler($xml) {
		$status = "result";
		$xmlroster = $xml->sub('query');
		foreach($xmlroster->subs as $item) {
			$groups = array();
			if ($item->name == 'item') {
				$jid = $item->attrs['jid']; //REQUIRED
				$name = $item->attrs['name']; //MAY
				$subscription = $item->attrs['subscription'];
				foreach($item->subs as $subitem) {
					if ($subitem->name == 'group') {
						$groups[] = $subitem->data;
					}
				}
				$contacts[] = array($jid, $subscription, $name, $groups); //Store for action if no errors happen
			} else {
				$status = "error";
			}
		}
		if ($status == "result") { //No errors, add contacts
			foreach($contacts as $contact) {
				$this->roster->addContact($contact[0], $contact[1], $contact[2], $contact[3]);
			}
		}
		if ($xml->attrs['type'] == 'set') {
			$this->send("<iq type=\"reply\" id=\"{$xml->attrs['id']}\" to=\"{$xml->attrs['from']}\" />");
		}
	}

	/**
	 * Session start handler
	 *
	 * @param string $xml
	 */
	protected function session_start_handler($xml) {
		$this->log->log("Session started");
		$this->session_started = true;
		$this->event('session_start');
	}

	/**
	 * TLS proceed handler
	 *
	 * @param string $xml
	 */
	protected function tls_proceed_handler($xml) {
		$this->log->log("Starting TLS encryption");
		stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_SSLv23_CLIENT);
		$this->reset();
	}

	/**
	* Retrieves the vcard
	*
	*/
	public function getVCard($jid = Null) {
		$id = $this->getID();
		$this->addIdHandler($id, 'vcard_get_handler');
		if($jid) {
			$this->send("<iq type='get' id='$id' to='$jid'><vCard xmlns='vcard-temp' /></iq>");
		} else {
			$this->send("<iq type='get' id='$id'><vCard xmlns='vcard-temp' /></iq>");
		}
	}

	/**
	* VCard retrieval handler
	*
	* @param XML Object $xml
	*/
	protected function vcard_get_handler($xml) {
		$vcard_array = array();
		$vcard = $xml->sub('vcard');
		// go through all of the sub elements and add them to the vcard array
		foreach ($vcard->subs as $sub) {
			if ($sub->subs) {
				$vcard_array[$sub->name] = array();
				foreach ($sub->subs as $sub_child) {
					$vcard_array[$sub->name][$sub_child->name] = $sub_child->data;
				}
			} else {
				$vcard_array[$sub->name] = $sub->data;
			}
		}
		$vcard_array['from'] = $xml->attrs['from'];
		$this->event('vcard', $vcard_array);
	}
	
	
	/**
	  * Register a new user.
	  *
	  * @param $entity
	  * Entity we want information about
	  */
	  public function registerNewUser($user_name, $password = NULL, $email, $name=NULL){
	        
	    $id = 'reg_' . $this->getID();
	    $xml = "<iq type='set' id='$id'>
	                <query xmlns='jabber:iq:register'>
	                    <username>" . $user_name . "</username>
	                    <password>" . $password . "</password>
	                    <email>" . $email . "</email>
	                    <name>" . $name . "</name>
	                </query>
	            </iq>";
	    $this->send($xml);
	    
	  }


	
	  /**
	   * Add contact to your roster
	   */
	  public function addRosterContact($jid, $name, $nickName="",$groups=array("Friends")){
	      // return if there is no jid specified
	      if(!$jid) return;
	      // set name to the jid if none is specified
	      if (!$name) { $name = $jid; }
	      $id = $this->getID();
	      $xml = "<iq type='set' id='$id'>";
	      $xml .= "<query xmlns='jabber:iq:roster'>";
	      $xml .= "<item jid='$jid' name='$name'>";
	      foreach ($groups as $group) {
	              $xml .= "<group>$group</group>";
	      }
	      $xml .= "</item>";
	      $xml .= "</query>";
	      $xml .= "</iq>";
	      
		$xml= <<<EOF
<presence to='{$jid}' type='subscribe'>
	<nick xmlns='http://jabber.org/protocol/nick'>{$nickName}</nick>
</presence>
EOF;
	      
	      $this->send($xml);
	  }
	
	  
	  /**
	   * accept friend request
	   * @param unknown_type $send_jid
	   * @param unknown_type $received_jid
	   */
	  public function acceptRosterRequest( $send_jid, $receive_jid,$send_name="",$receive_name="" ){
	  	 	
		$xml= <<<EOF
<presence from="{$send_jid}" to="{$receive_jid}" type="subscribed">
	<nick xmlns='http://jabber.org/protocol/nick'>{$send_name}</nick>
</presence> 
<presence from="{$receive_jid}" to="{$send_jid}" type="subscribed">
	<nick xmlns='http://jabber.org/protocol/nick'>{$receive_name}</nick>
</presence> 
EOF;
		$this->send($xml);
	  	
	  }
	
	  /**
	  * Contact you wish to remove
	  * @param $jid
	  * 
	  */
	  public function deleteRosterContact($jid) {
	    $id = $this->getID();
	    $xml = "<iq type='set' id='$id'>";
	    $xml .= "<query xmlns='jabber:iq:roster'>";
	    $xml .= "<item jid='" . $jid . "' subscription='remove' />";
	    $xml .= "</query>";
	    $xml .= "</iq>";
	    $this->send($xml);
	  }

	/**
	 * send group message
	 * @param unknown_type $to
	 * @param unknown_type $body
	 * @param unknown_type $type
	 * @param unknown_type $subject
	 * @param unknown_type $payload
	 */
	public function roomMessage($jid,$room_jid, $body, $subject = null, $payload = null, $user_name=null) {

		if( $user_name ){
//			$present_roomId=$room_jid."/".$user_name;
			$present_roomId=$room_jid."/".$user_name."_";  //加“_”是因为防止同一个帐号在两个地方用同样的nickname登录房间，会有一个地方会退出登录的
		}
		else{
			$present_roomId=$room_jid;
		}
		
$id=$this->getID();	

		$out= <<<EOF
<presence 
    from='{$jid}'
    to='{$present_roomId}'>
  <x xmlns='http://jabber.org/protocol/muc'/>  
</presence>

EOF;

		$jid	  = htmlspecialchars($jid);
		$body	= htmlspecialchars($body);
		$subject = htmlspecialchars($subject);
		
		$out .= "<message from=\"{$jid}\" to=\"{$room_jid}\" type='groupchat'>";
		if($subject) $out .= "<subject>$subject</subject>";
		$out .= "<body>$body</body>";
		if($payload) $out .= $payload;
		$out .= "</message>";
		
		
		$this->send($out);		
		
		
	}	  
	  
	  
	  
	  /**
	   * create chat group
	   * @param unknown_type $jid, 创建者的jid
	   * @param unknown_type $room_jid，欲创建聊天室的jid
	   * 
	   * 举例说明：
	   * 在类外调用本方法
	   * 		
	    	//修改房间的默认设置
			$room_setting=array('muc#roomconfig_roomname'=>$testName,'muc#roomconfig_roomdesc'=>$testDesc,'muc#roomconfig_changesubject'=>1);
			
			//生成chatroom，并保存chatroom的配置				
			$this->_conn->createChatRoom($jid,$test_id,$real_name,$room_setting,$this);
			
			//chatroom的配置发送到服务器, 在本类中需要public 属性room_xml
			$this->_conn->sendChatroom_setting($jid,$test_id,$real_name,$this->room_xml);
	   * 
	   */
	  public function createChatRoom($jid, $room_jid,$real_name,$room_setting=array(),$ref_obj){
	  	
	  	$this->refObj=$ref_obj;
	    $this->room_setting=$room_setting;
	    $id=$this->getID();
	    
	    $this->from=$jid;
	    $this->to=$room_jid;
	  	

		$xml= <<<EOF
<presence 
    from='{$jid}'
    to='{$room_jid}/{$real_name}'>
  <x xmlns='http://jabber.org/protocol/muc'/>  
</presence>
<iq from='{$jid}'
    id="{$id}"
    to='{$room_jid}'
    type='get'>
  <query xmlns='http://jabber.org/protocol/muc#owner'/>
</iq>
EOF;

	    		
		$this->addIdHandler($id, 'setChatroom');	
		$this->send($xml);	
	  }
	  
	/**
	 * set chatroom setting
	 * @param unknown_type $xml
	 */
	public function setChatroom($xml){
		
		$xml->attrs['type']="set";
		$xml->attrs['from']=$this->from;
		$xml->attrs['to']=$this->to;
		$xml->subs[0]->subs[0]->attrs['type']='submit';
		foreach( $xml->subs[0]->subs[0]->subs as &$node ){
			if( isset($node->attrs['var']) && isset( $this->room_setting[$node->attrs['var']] ) ){
				$node->subs[0]->data=$this->room_setting[$node->attrs['var']];
			}
		}
		
		$this->refObj->room_xml=$xml->toString();
	}
	
	/**
	 * 调用这个方法前先调用setChatroom（）设置了房间的属性
	 */
	public function sendChatroomSetting($jid, $room_jid,$real_name,$room_xml){
		
		$xml= <<<EOF
<presence 
    from='{$jid}'
    to='{$room_jid}/{$real_name}'>
  <x xmlns='http://jabber.org/protocol/muc'/>  
</presence>
EOF;
		
		$xml.=$room_xml;
		$this->send($xml);
		
	}
	
	  
	  
	  
	  /**
	   * kick user out to chat room
	   * @param unknown_type $jid, 创建者的jid
	   * @param unknown_type $room_jid，欲创建聊天室的jid
	   */
	  public function kickUserOutToChatRoom($jid, $room_jid,$real_name,$kick_names=array()){
	  	

		$xml= <<<EOF
<presence 
    from='{$jid}'
    to='{$room_jid}/{$real_name}'>
  <x xmlns='http://jabber.org/protocol/muc'/>  
</presence>
EOF;

		foreach( $kick_names as $kick_name ){
		$id=$this->getID();	
			
		$xml.= <<<EOF
<iq from='{$jid}'
    id='{$id}'
    to='{$room_jid}'
    type='set'>
  <query xmlns='http://jabber.org/protocol/muc#admin'>
    <item nick='{$kick_name}' role='none'>
      <reason>管理员请出你出房间</reason>
    </item>
  </query>
</iq>
EOF;
			
		}
	    		
		$this->send($xml);	
	  }	  
	  
	  
	  /**
	  *
	  * @param XML Object $xml
	  */
	  protected function delete_roster_contact_handler($xml) {
	    // do any handling you wish here
	    $this->event('contact_removed');
	  }		
		
      public function getJid(){
        return $this->jid;
      }
      
      

	
	protected function printXml($xml){
		print_r($xml);
	}
	
      

}
