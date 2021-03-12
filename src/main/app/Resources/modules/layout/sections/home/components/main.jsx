import React from 'react'
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

const HomeMain = (props) =>
  <Routes
    redirect={[
      {from: '/', exact: true, to: '/unavailable', disabled: !props.unavailable},
      {from: '/home', to: '/unavailable', disabled: !props.unavailable},
      {from: '/unavailable', to: '/', disabled: props.unavailable},

      {from: '/', exact: true, to: '/login',   disabled: props.hasHome || props.authenticated},
      {from: '/', exact: true, to: '/home',    disabled: props.unavailable || !props.hasHome},
      {from: '/', exact: true, to: '/desktop', disabled: props.unavailable || props.hasHome || !props.authenticated},

      {from: '/login', to: '/', disabled: !props.authenticated}
    ]}
    routes={[
      {
        path: '/unavailable',
        disabled: !props.unavailable,
        render: () => {
          const Disabled = (
            <HomeDisabled
              disabled={props.disabled}
              maintenance={props.maintenance}
              maintenanceMessage={props.maintenanceMessage}
              authenticated={props.authenticated}
              restrictions={props.restrictions}
              reactivate={props.reactivate}
            />
          )

          return Disabled
        }
      }, {
        path: '/reset_password',
        disabled: props.authenticated || !props.changePassword,
        component: SendPassword
      }, {
        path: '/newpassword/:hash',
        component: NewPassword
      }, {
        path: '/login',
        disabled: props.authenticated,
        component: HomeLogin
      }, {
        path: '/registration',
        disabled: props.unavailable || !props.selfRegistration || props.authenticated,
        component: HomeRegistration
      }, { // TODO : disable if no sso
        path: '/external/:app',
        render: (routeProps) => {
          const LinkAccount = (
            <HomeExternalAccount
              isAuthenticated={props.authenticated}
              selfRegistration={props.selfRegistration}
              serviceName={routeProps.match.params.app}
              linkExternalAccount={props.linkExternalAccount}
            />
          )

          return LinkAccount
        }
      }, {
        path: '/home',
        disabled: props.unavailable || !props.hasHome,
        onEnter: () => props.openHome(props.homeType, props.homeData),
        render: () => {
          const Home = (
            <HomeContent
              type={props.homeType}
              content={props.homeData}
            />
          )

          return Home
        }
      }
    ]}
  />

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
