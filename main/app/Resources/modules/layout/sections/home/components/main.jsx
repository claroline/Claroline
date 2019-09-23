import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router/components/routes'

import {HomeContent} from '#/main/app/layout/sections/home/components/content'
import {HomeMaintenance} from '#/main/app/layout/sections/home/components/maintenance'
import {HomeLogin} from '#/main/app/layout/sections/home/components/login'
import {SendPassword} from '#/main/app/layout/sections/home/components/send-password'
import {NewPassword} from '#/main/app/layout/sections/home/components/new-password'
import {HomeRegistration} from '#/main/app/layout/sections/home/components/registration'
import {HomeExternalAccount} from '#/main/app/layout/sections/home/components/external-account'

// TODO : move all security sections in main/authentication

const HomeMain = (props) =>
  <Routes
    redirect={[
      {from: '/', exact: true, to: '/home',        disabled: props.maintenance || !props.hasHome},
      {from: '/', exact: true, to: '/maintenance', disabled: !props.maintenance || props.isAuthenticated},
      {from: '/', exact: true, to: '/login',       disabled: props.hasHome || props.isAuthenticated},
      {from: '/', exact: true, to: '/desktop',     disabled: props.hasHome || !props.isAuthenticated}
    ]}
    routes={[
      {
        path: '/maintenance',
        disabled: !props.maintenance || props.isAuthenticated,
        component: HomeMaintenance
      }, {
        path: '/reset_password',
        component: SendPassword
      },
      {
        path: '/newpassword/:hash',
        component: NewPassword
      }, {
        path: '/login',
        disabled: props.isAuthenticated,
        component: HomeLogin
      }, {
        path: '/registration',
        disabled: !props.selfRegistration ||props.isAuthenticated,
        component: HomeRegistration
      }, { // TODO : disable if no sso
        path: '/external/:app',
        render: (routeProps) => {
          const LinkAccount = (
            <HomeExternalAccount
              isAuthenticated={props.isAuthenticated}
              selfRegistration={props.selfRegistration}
              serviceName={routeProps.match.params.app}
              linkExternalAccount={props.linkExternalAccount}
            />
          )

          return LinkAccount
        }
      }, {
        path: '/home',
        disabled: !props.hasHome,
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
  maintenance: T.bool.isRequired,
  isAuthenticated: T.bool.isRequired,
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
