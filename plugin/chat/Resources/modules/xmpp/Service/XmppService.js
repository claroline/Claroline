/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/* global Strophe */
/* global $pres */
/* global Routing */

export default class XmppService {
  constructor ($rootScope, $http, $log) {
    this.protocol = XmppService._getGlobal('xmppSsl') ? 'https' : 'http'
    this.$rootScope = $rootScope
    this.$http = $http
    this.$log = $log
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
      boshService: `${this.protocol}://${XmppService._getGlobal('xmppHost')}:${XmppService._getGlobal('boshPort')}/http-bind`,
      canChat: false,
      connected: false,
      busy: false, // equals "connecting"
      adminConnected: false
    }
    this.adminUsername = XmppService._getGlobal('chatAdminUsername')
    this.adminPassword = XmppService._getGlobal('chatAdminPassword')
    this._connectionCallback = this._connectionCallback.bind(this)
    this._adminConnectionCallback = this._adminConnectionCallback.bind(this)
    this._connectedCallback = () => {
    }
  }

  _connectionCallback (status) {
    if (status === Strophe.Status.CONNECTED) {
      this.$log.log('Connected')
      this.config['connection'].send($pres().c('priority').t('-1'))
      this.config['connected'] = true
      this.config['busy'] = false
      this.refreshScope()
      this._connectedCallback()
    } else if (status === Strophe.Status.CONNFAIL) {
      this.$log.log('Connection failed !')
      this.config['connected'] = false
      this.config['busy'] = false
    } else if (status === Strophe.Status.DISCONNECTED) {
      this.$log.log('Disconnected')
      this.config['connected'] = false
      this.config['busy'] = false
    } else if (status === Strophe.Status.CONNECTING) {
      this.config['busy'] = true
      this.$log.log('Connecting...')
    } else if (status === Strophe.Status.DISCONNECTING) {
      this.config['busy'] = false
      this.$log.log('Disconnecting...')
    }
  }

  _adminConnectionCallback (status) {
    if (status === Strophe.Status.CONNECTED) {
      this.$log.log('admin Connected')
      this.config['adminConnection'].send($pres().c('priority').t('-1'))
      this.config['adminConnected'] = true
      this.refreshScope()
    } else if (status === Strophe.Status.CONNFAIL) {
      this.$log.log('admin Connection failed !')
    } else if (status === Strophe.Status.DISCONNECTED) {
      this.$log.log('admin Disconnected')
    } else if (status === Strophe.Status.CONNECTING) {
      this.$log.log('admin Connecting...')
    } else if (status === Strophe.Status.DISCONNECTING) {
      this.$log.log('admin Disconnecting...')
    }
  }

  getConfig () {
    return this.config
  }

  setConnectedCallback (callback) {
    this._connectedCallback = callback
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
            this.config['connection'] = new Strophe.Connection(this.config['boshService'])
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

  connectWithAdmin () {
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

            this.config['adminConnection'] = new Strophe.Connection(this.config['boshService'])
            this.config['adminConnection'].connect(
              `${this.adminUsername}@${this.config['xmppHost']}`,
              this.adminPassword,
              this._adminConnectionCallback
            )

            this.config['connection'] = new Strophe.Connection(this.config['boshService'])
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

  refreshScope () {
    this.$rootScope.$apply()
  }
}
