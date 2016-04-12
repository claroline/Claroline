/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

export default class XmppService {
  constructor ($http) {
    this.$http = $http
    this.config = {
      adminConnection: null,
      connection: null,
      username: null,
      password: null,
      firstName: null,
      lastName: null,
      fullName: null,
      color: null,
      xmppHost: XmppService._getGlobal('xmppHost'),
      boshPort: XmppService._getGlobal('boshPort'),
      boshService: `http://${XmppService._getGlobal('xmppHost')}:${XmppService._getGlobal('boshPort')}/http-bind`,
      canChat: false,
      connected: false,
      busy: false,
      adminConnected: false
    }
    this.adminUsername = XmppService._getGlobal('chatAdminUsername')
    this.adminPassword = XmppService._getGlobal('chatAdminPassword')
    this._connectionCallback = this._connectionCallback.bind(this)
    this._adminConnectionCallback = this._adminConnectionCallback.bind(this)
  }

  _connectionCallback (status) {

    if (status === Strophe.Status.CONNECTED) {
      console.log('Connected')
      this.config['connection'].send($pres().c('priority').t('-1'))
      this.config['connected'] = true
      this.config['busy'] = false
      //$rootScope.$broadcast('xmppConnectedEvent')
    } else if (status === Strophe.Status.CONNFAIL) {
      console.log('Connection failed !')
      this.config['connected'] = false
      this.config['busy'] = false
    } else if (status === Strophe.Status.DISCONNECTED) {
      console.log('Disconnected')
      this.config['connected'] = false
      this.config['busy'] = false
    } else if (status === Strophe.Status.CONNECTING) {
      this.config['busy'] = true
      console.log('Connecting...')
    } else if (status === Strophe.Status.DISCONNECTING) {
      this.config['busy'] = true
      console.log('Disconnecting...')
    }
  }

  _adminConnectionCallback (status) {

    if (status === Strophe.Status.CONNECTED) {
      console.log('admin Connected')
      this.config['adminConnection'].send($pres().c('priority').t('-1'))
      this.config['adminConnected'] = true
      //this.config['busy'] = false
    } else if (status === Strophe.Status.CONNFAIL) {
      console.log('admin Connection failed !')
      //this.config['connected'] = false
      //this.config['busy'] = false
    } else if (status === Strophe.Status.DISCONNECTED) {
      console.log('admin Disconnected')
      //this.config['connected'] = false
      //this.config['busy'] = false
    } else if (status === Strophe.Status.CONNECTING) {
      //this.config['busy'] = true
      console.log('admin Connecting...')
    } else if (status === Strophe.Status.DISCONNECTING) {
      //this.config['busy'] = true
      console.log('admin Disconnecting...')
    }
  }

  getConfig () {
    return this.config
  }

  connect () {
    if (!this.config['connected'] && !this.config['busy']) {
      const route = Routing.generate('api_get_xmpp_options')

      this.$http.get(route).then(datas => {

        if (datas['status'] === 200) {
          this.config['canChat'] = datas['data']['canChat']

          if (datas['data']['canChat']) {
            this.config['username'] = datas['data']['chatUsername']
            this.config['password'] = datas['data']['chatPassword']
            this.config['firstName'] = datas['data']['firstName']
            this.config['lastName'] = datas['data']['lastName']
            this.config['fullName'] = `${datas['data']['firstName']} ${datas['data']['lastName']}`
            this.config['color'] = datas['data']['chatColor']

            this.config['adminConnection'] = new Strophe.Connection(this.config['boshService']);
            this.config['adminConnection'].connect(
              `${this.adminUsername}@${this.config['xmppHost']}`,
              this.adminPassword,
              this._adminConnectionCallback
            )

            this.config['connection'] = new Strophe.Connection(this.config['boshService']);
            this.config['connection'].connect(
              `${this.config['username']}@${this.config['xmppHost']}`,
              this.config['password'],
              this._connectionCallback
            )
          }
        }
      })
    }
  }

  static _getGlobal (name) {
    if (typeof window[name] === 'undefined') {
      throw new Error(
        `Expected ${name} to be exposed in a window.${name} variable`
      )
    }

    return window[name]
  }
}