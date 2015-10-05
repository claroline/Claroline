/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

(function () {
    'use strict';
    
    var xmppHost;
    var xmppPort;
    var connection;
    
    function connection()
    {
        xmppHost = $('#chat-datas-box').data('xmpp-host');
        xmppPort = $('#chat-datas-box').data('xmpp-port');
        
        console.log(xmppHost + ' - ' + xmppPort);
        connection = new Strophe.Connection(xmppHost + ':' + xmppPort);
    }
    
    connection();
})();