import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router/components/routes'

import {HomeContent} from '#/main/app/layout/sections/home/components/content'
import {HomeMaintenance} from '#/main/app/layout/sections/home/components/maintenance'
import {HomeLogin} from '#/main/app/layout/sections/home/components/login'
import {HomeRegistration} from '#/main/app/layout/sections/home/components/registration'

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
        path: '/login',
        disabled: props.isAuthenticated,
        component: HomeLogin
      }, {
        path: '/registration',
        disabled: props.isAuthenticated,
        component: HomeRegistration
      }, {
        path: '/home',
        disabled: !props.hasHome,
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
  hasHome: T.bool.isRequired,
  homeType: T.string.isRequired,
  homeData: T.string
}

export {
  HomeMain
}
