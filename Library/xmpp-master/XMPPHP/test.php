<?php

/* 封装有关xmpp的操作xmpp
 * @package
 * @category
 * @author newjueqi
 * 
 * 管理员的操作文档：http://xmpp.org/extensions/xep-0133.html
 */


require_once 'XMPP.php';


class testxmpp 
{
	//xmpp服务器管理员账户
	protected $_adminUsername="";
	
	//xmpp服务器管理员密码
	protected $_adminPassword="";	
	
	protected $_ip=""; //xmpp服务器ip
	
	protected $_port=""; //xmpp服务器端口
	
	protected $_conn=null;
	
	protected $ojb=null;
	
	public $roomSettingXml="";
	
	public $errormsg="";
	
	public function __construct() {
		
		$this->obj = &get_instance();
		
		$this->_adminUsername=config_item('xmpp_admin_username');
		$this->_adminPassword=config_item('xmpp_admin_password');
		$this->_ip=config_item('xmpp_server_ip');
		$this->_port=config_item('xmpp_server_port');
		
   		$this->obj->load->model('User_model');  
   		$this->obj->load->model('Event_model');       
		
	}
	/**
	 * 连接xmpp服务器
	 * @param unknown_type $username 连接的用户名
	 * @param unknown_type $password 连接的帐号
	 */
	protected function getConnect($username=null,$password=null){
		
		if( $username &&  $password){
			$this->_conn=new XMPPHP_XMPP($this->_ip, $this->_port, $username, $password,  'xmpphp');
		}else{
			$this->_conn=new XMPPHP_XMPP($this->_ip, $this->_port, $this->_adminUsername, $this->_adminPassword,  'xmpphp');
		}
		$this->_conn->useEncryption(false);
	    $this->_conn->connect();
	    $this->_conn->processUntil('session_start');
	    $this->_conn->presence();
			
	} 
	
	/**
	 * 断开xmpp服务器的连接
	 */
	protected function disconnect(){
		$this->_conn->disconnect();	
	}

	
	/**
	 * 在xmpp服务器中注册新用户
	 * @param unknown_type $uid:用户的id,不是jid
	 * @param unknown_type $password
	 * @param unknown_type $email
	 */
	public function registerNewUser( $uid ){
		
		//当配置不启用聊天服务器的时候，去启用聊天服务
		if( $this->enableXmppServer()==false ){
			return;
		}
		
		try{
			$this->getConnect();
			$pwd=$this->getPwd($uid);
			$email=$this->obj->User_model->fetchOneInTable('ek_user_account','email','id',$uid);
			$realname=$this->obj->User_model->fetchOneInTable('ek_user_account','username','id',$uid);
			$this->_conn->registerNewUser($uid, $pwd , $email,$realname);
			$this->disconnect();
			
		}catch (XMPPHP_Exception $e) {
	    	log_message('error',$e->getMessage());
		}
    }
    
	
	/**
	 * 添加好友
	 * @param unknown_type $sender_id 发送者的id
	 * @param unknown_type $receiver_id 接收者的id
	 */
    public function addRoster( $sender_id,$receiver_id ){
		
    	//当配置不启用聊天服务器的时候，去启用聊天服务
		if( $this->enableXmppServer()==false ){
			return;
		}    	
    	
    	try{
    		
    		$sender_password= $this->obj->User_model->fetchOneInTable('ek_user_profile','xmpp_pwd','uid',$sender_id);
    		$receiver_password= $this->obj->User_model->fetchOneInTable('ek_user_profile','xmpp_pwd','uid',$receiver_id);
    		
    		//获取对方的真名
    		$sender_realname=$this->obj->User_model->fetchOneInTable('ek_user_profile','realname','uid',$sender_id);
    		$receiver_realname=$this->obj->User_model->fetchOneInTable('ek_user_profile','realname','uid',$receiver_id);
    		
    		//链接xmpp服务器
			$this->getConnect($sender_id,$sender_password);    	
    		
			//添加好友
			$this->_conn->addRosterContact(  $this->getJid($receiver_id)
										   ,$receiver_realname );
			$this->disconnect();

    		//链接xmpp服务器
			$this->getConnect($receiver_id,$receiver_password);    	
    		
			//添加好友
			$this->_conn->addRosterContact(  $this->getJid($sender_id)
										   ,$sender_realname );
			$this->disconnect();			
			
			
//			$this->getConnect($sender_id,$sender_password);    	
//			//模拟通过好友请求
//			$this->_conn->acceptRosterRequest(  $this->getJid($sender_id),$this->getJid($receiver_id)
//												,$sender_realname,$receiver_realname);
//			$this->disconnect();
//			
//			$this->getConnect($receiver_id,$receiver_password);    		
//			//模拟通过好友请求
//			$this->_conn->acceptRosterRequest(  $this->getJid($receiver_id),$this->getJid($sender_id)
//												,$receiver_realname,$sender_realname);
//			$this->disconnect();
			
		}catch (XMPPHP_Exception $e) {
	    	log_message('error',$e->getMessage());
		}		    	
    }		

    /**
     * 删除好友，例如a要删除好友b
     * 
     * @param unknown_type $uid:  a的id
     * @param unknown_type $fuid：b的id 
     */
    public function deleteRosterContact( $uid,$fuid ){
    	
    	//当配置不启用聊天服务器的时候，去启用聊天服务
		if( $this->enableXmppServer()==false ){
			return;
		}      	
    	
    	try{
			
    		$password=$this->getPwd($uid);
			$this->getConnect($uid,$password);
			$this->_conn->deleteRosterContact($this->getJid($fuid));
			$this->disconnect();
			
			$password=$this->getPwd($fuid);
			$this->getConnect($fuid,$password);
			$this->_conn->deleteRosterContact($this->getJid($uid));			
			
		}catch (XMPPHP_Exception $e) {
	    	log_message('error',$e->getMessage());
		}    	
		
    }
	
    /**
     * 创建一个聊天室
     */
	public function createEventChatRoom($uid,$event_id){
		
    	//当配置不启用聊天服务器的时候，去启用聊天服务
		if( $this->enableXmppServer()==false ){
			return;
		}  		
		
    	try{
			
    		$password=$this->getPwd($uid);
			
			$realname= $uid;
			$eventName= $this->obj->Event_model->fetchOneInTable('ek_event','title','id',$event_id);
			$eventDesc= $this->obj->Event_model->fetchOneInTable('ek_event','desc','id',$event_id);
			
			$event_id=$this->getEventJid($event_id);
			
			//修改房间的默认设置
			$roomSetting=array('muc#roomconfig_roomname'=>$eventName,'muc#roomconfig_roomdesc'=>$eventDesc,'muc#roomconfig_changesubject'=>1);
			
			//生成chatroom，并保存chatroom的配置
			$this->getConnect($uid,$password);
			$this->_conn->createChatRoom($this->getJid($uid),$event_id,$realname,$roomSetting,$this);
			$this->disconnect();
			
			//chatroom的配置发送到服务器
			$this->getConnect($uid,$password);
			$this->_conn->sendChatroomSetting($this->getJid($uid),$event_id,$realname,$this->roomSettingXml);
			$this->disconnect();			
			
		}catch (XMPPHP_Exception $e) {
	    	log_message('error',$e->getMessage());
		}  		
		
	}   
	
	/**
	 * 创建一个临时的聊天室
	 * @param unknown_type $uid
	 */
	public function createTempChatRoom( $uid ){
		
    	//当配置不启用聊天服务器的时候，去启用聊天服务
		if( $this->enableXmppServer()==false ){
			return;
		}  		
		
    	try{
			
    		$password=$this->getPwd($uid);
			
    		$id=time();
			$realname= $uid;
			$eventName= "";
			$eventDesc= "";
			
			$id=$this->getTempChatJid($id);
//			$id="temp_23243424374@conference.ay12091711261312e7366";
			
			//修改房间的默认设置
			$roomSetting=array('muc#roomconfig_roomname'=>$eventName,'muc#roomconfig_roomdesc'=>$eventDesc,'muc#roomconfig_changesubject'=>1);
			
			//生成chatroom，并保存chatroom的配置
			$this->getConnect($uid,$password);
			$this->_conn->createChatRoom($this->getJid($uid),$id,$realname,$roomSetting,$this);
			$this->disconnect();
			
			//chatroom的配置发送到服务器
			$this->getConnect($uid,$password);
			$this->_conn->sendChatroomSetting($this->getJid($uid),$id,$realname,$this->roomSettingXml);
			$this->disconnect();	
			
		}catch (XMPPHP_Exception $e) {
	    	log_message('error',$e->getMessage());
		} 	

		return $id;
	}
    
	/**
	 * 剔除用户出房间
	 * @param unknown_type $uid
	 * @param unknown_type $roomJid
	 * @param unknown_type $kickerJid
	 */
	public function kickUserOutToChatRoom( $uid,$roomJid,$kickerUids ){
    	//当配置不启用聊天服务器的时候，去启用聊天服务
		if( $this->enableXmppServer()==false ){
			return;
		}  

    	try{
			$realname= $uid;
    		$password=$this->getPwd($uid);
			$jid=$this->getJid($uid);
			
			if( !is_array($kickerUids) ){
				$kickerUids=array($kickerUids);
			}
			
			//生成chatroom，并保存chatroom的配置
			$this->getConnect($uid,$password);
			$this->_conn->kickUserOutToChatRoom($jid,$roomJid,$realname,$kickerUids);
			$this->disconnect();
			
			
		}catch (XMPPHP_Exception $e) {
	    	log_message('error',$e->getMessage());
		} 			
		
	}
	
	/**
	 * 给某个用户发消息
	 * @param unknown_type $uid
	 * @param unknown_type $fuid
	 * @param unknown_type $message
	 * @param unknown_type $password
	 */
	public function sendMessage( $jid,$fjid,$message,$password=null ){
    	//当配置不启用聊天服务器的时候，去启用聊天服务
		if( $this->enableXmppServer()==false ){
			return;
		}  		
		
    	try{
			
    	    if(  !$password ){
    			$password=$this->getPwd($this->getUid($jid));
    		}
    		
			//生成chatroom，并保存chatroom的配置
			$this->getConnect($jid,$password);
			$this->_conn->message($fjid,$message);
			$this->disconnect();
			
		}catch (XMPPHP_Exception $e) {
	    	log_message('error',$e->getMessage());
	    	$this->errormsg=$e->getMessage();
	    	return false;
		} 			
		
		return true;
	}
	
	/**
	 * 给某个群组发消息
	 * @param unknown_type $uid
	 * @param unknown_type $fuid
	 * @param unknown_type $message
	 * @param unknown_type $password
	 */
	public function sendGroupMessage( $jid,$roomId,$message,$password=null ){
    	//当配置不启用聊天服务器的时候，去启用聊天服务
		if( $this->enableXmppServer()==false ){
			return;
		}  		
		
    	try{
			
    	    if( !$password ){
    			$password=$this->getPwd($this->getUid($jid));
    	    }
    		
//    		$realname= $this->obj->User_model->fetchOneInTable('ek_user_account','username','id',$this->getUid($jid));
    		
			//生成chatroom，并保存chatroom的配置
			$this->getConnect($jid,$password);
			$this->_conn->roomMessage($jid,$roomId,$message,null,null,$this->getUid($jid));
			$this->disconnect();
			
		}catch (XMPPHP_Exception $e) {
	    	log_message('error',$e->getMessage());
	    	return false;
		} 		
		return true;	
	}	    
    
	/**
	 * 获取服务主机名，例如ekeo@pc--20120330uig，则返回“pc--20120330uig”
	 */
	protected function getServerDomain(){
		
		return config_item("XMPP_servername");
	}
	
	/**
	 * 生成jid
	 * @param unknown_type $uid
	 */
	public function getJid($uid){
		return $uid."@".$this->getServerDomain();
	}
	
	/**
	 * 生成活动的event_id
	 * @param unknown_type $event_id
	 */
	public function getEventJid( $event_id ){
		return "event_".$event_id."@".config_item("XMPP_chat_servername");
	}
	
	/**
	 * 生成临时聊天的event_id
	 * @param unknown_type $event_id
	 */
	public function getTempChatJid( $event_id ){
		return "temp_".$event_id."@".config_item("XMPP_chat_servername");
	}	
	
	/**
	 * 生成地标的id
	 * @param unknown_type $place_id
	 */
	protected function getPlaceJid( $place_id ){
		return "place_".$place_id."@".$this->getServerDomain();
	}

	/**
	 * 获取服务器上的用户的密码
	 * @param unknown_type $uid
	 */
	public function getPwd($uid){
		$this->obj->load->model('Chat_model');       
		return $this->obj->Chat_model->getPwd($uid);
	}
	
	/**
	 * 通过配置文件控制是否连接xmpp服务器
	 */
	public function enableXmppServer(){
		if( config_item("enable_xmpp_server")==1 ){
			return true;
		}
		return false;
	}
	
	/**
	 * 从jid获取用户的uid
	 * @param unknown_type $jid
	 */
	public function getUid($jid){
		return substr($jid,0,strpos($jid,"@"));
	}
	
	
}

/* End of file Layout.php */
/* Location: */
