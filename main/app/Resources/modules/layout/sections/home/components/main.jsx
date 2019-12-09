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
      {from: '/', exact: true, to: '/home',        disabled: (props.maintenance && !props.authenticated) || !props.hasHome},
      {from: '/', exact: true, to: '/unavailable', disabled: !props.maintenance || props.authenticated},
      {from: '/', exact: true, to: '/login',       disabled: props.hasHome || props.authenticated},
      {from: '/', exact: true, to: '/desktop',     disabled: props.hasHome || !props.authenticated}
    ]}
    routes={[
      {
        path: '/unavailable',
        component: HomeDisabled,
        disabled: !props.maintenance && !props.disabled,
        render: () => {
          const Disabled = (
            <HomeDisabled
              disabled={props.disabled}
              maintenance={props.maintenance}
              authenticated={props.authenticated}
            />
          )

          return Disabled
        }
      }, {
        path: '/reset_password',
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
        disabled: !props.selfRegistration || props.authenticated || props.maintenance,
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
        disabled: (props.maintenance && !props.authenticated) || !props.hasHome,
        onEnter: () => props.openHome(props.homeType),
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
  disabled: T.bool,
  maintenance: T.bool.isRequired,
  authenticated: T.bool.isRequired,
  selfRegistration: T.bool.isRequired,
  hasHome: T.bool.isRequired,
  homeType: T.string.isRequired,
  homeData: T.string,
  openHome: T.func.isRequired,
  linkExternalAccount: T.func.isRequired
}

export {
  HomeMain
}
