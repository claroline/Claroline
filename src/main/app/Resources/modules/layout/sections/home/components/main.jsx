import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router/components/routes'

import {HomeContent} from '#/main/app/layout/sections/home/components/content'
import {HomeDisabled} from '#/main/app/layout/sections/home/components/disabled'
import {HomeLogin} from '#/main/app/layout/sections/home/components/login'
import {SendPassword} from '#/main/app/layout/sections/home/components/send-password'
import {NewPassword} from '#/main/app/layout/sections/home/components/new-password'
import {HomeRegistration} from '#/main/app/layout/sections/home/components/registration'
import {HomeExternalAccount} from '#/main/app/layout/sections/home/components/external-account'

// TODO : move all security sections in main/authentication

class HomeMain extends Component {
  componentDidMount() {
    this.props.open()
  }

  render() {
    return (
      <Routes
        redirect={[
          {from: '/', exact: true, to: '/unavailable', disabled: !this.props.unavailable},
          {from: '/home', to: '/unavailable', disabled: !this.props.unavailable},
          {from: '/unavailable', to: '/', disabled: this.props.unavailable},

          {from: '/', exact: true, to: '/login',   disabled: this.props.hasHome || this.props.authenticated},
          {from: '/', exact: true, to: '/home',    disabled: this.props.unavailable || !this.props.hasHome},
          {from: '/', exact: true, to: '/desktop', disabled: this.props.unavailable || this.props.hasHome || !this.props.authenticated},

          {from: '/login', to: '/', disabled: !this.props.authenticated},
          {from: '/registration', to: '/', disabled: !this.props.unavailable || this.props.selfRegistration || !this.props.authenticated}
        ]}
        routes={[
          {
            path: '/unavailable',
            disabled: !this.props.unavailable,
            render: () => (
              <HomeDisabled
                disabled={this.props.disabled}
                maintenance={this.props.maintenance}
                maintenanceMessage={this.props.maintenanceMessage}
                authenticated={this.props.authenticated}
                restrictions={this.props.restrictions}
                reactivate={this.props.reactivate}
              />
            )
          }, {
            path: '/reset_password',
            disabled: this.props.authenticated || !this.props.changePassword,
            component: SendPassword
          }, {
            path: '/newpassword/:hash',
            component: NewPassword
          }, {
            path: '/login/:forceInternalAccount(account)?',
            disabled: this.props.authenticated,
            component: HomeLogin
          }, {
            path: '/registration',
            disabled: this.props.unavailable || !this.props.selfRegistration || this.props.authenticated,
            component: HomeRegistration
          }, { // TODO : disable if no sso
            path: '/external/:app',
            render: (routeProps) => (
              <HomeExternalAccount
                isAuthenticated={this.props.authenticated}
                selfRegistration={this.props.selfRegistration}
                serviceName={routeProps.match.params.app}
                linkExternalAccount={this.props.linkExternalAccount}
              />
            )
          }, {
            path: '/home',
            disabled: this.props.unavailable || !this.props.hasHome,
            onEnter: () => this.props.openHome(this.props.homeType, this.props.homeData),
            render: () => (
              <HomeContent
                type={this.props.homeType}
                content={this.props.homeData}
              />
            )
          }
        ]}
      />
    )
  }
}

HomeMain.propTypes = {
  unavailable: T.bool.isRequired,
  disabled: T.bool.isRequired,
  maintenance: T.bool.isRequired,
  maintenanceMessage: T.string,
  authenticated: T.bool.isRequired,
  selfRegistration: T.bool.isRequired,
  changePassword: T.bool.isRequired,
  hasHome: T.bool.isRequired,
  homeType: T.string.isRequired,
  homeData: T.string,
  open: T.func.isRequired,
  openHome: T.func.isRequired,
  linkExternalAccount: T.func.isRequired,
  restrictions: T.shape({
    disabled: T.bool,
    dates: T.arrayOf(T.string)
  }),
  reactivate: T.func.isRequired
}

export {
  HomeMain
}
