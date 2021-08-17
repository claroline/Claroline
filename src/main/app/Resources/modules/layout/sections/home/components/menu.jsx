import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {Routes} from '#/main/app/router'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {LINK_BUTTON} from '#/main/app/buttons'

import {MenuMain} from '#/main/app/layout/menu/containers/main'
import {MenuSection} from '#/main/app/layout/menu/components/section'
import {ToolMenu} from '#/main/core/tool/containers/menu'

import {constants} from '#/main/app/layout/sections/home/constants'

const HomeSection = (props) =>
  <Routes
    routes={[
      {
        path: '/home',
        render: () => (
          <ToolMenu
            opened={true}
            toggle={() => true}
            autoClose={props.autoClose}
          />
        ),
        disabled: constants.HOME_TYPE_TOOL !== props.homeType
      }, {
        path: '/login',
        render: () => (
          <MenuSection
            title={trans('login')}
            opened={true}
            toggle={() => true}
            autoClose={props.autoClose}
          />
        )
      }, {
        path: '/registration',
        render: () => (
          <MenuSection
            title={trans('registration')}
            opened={true}
            toggle={() => true}
            autoClose={props.autoClose}
          />
        )
      }, {
        path: '/',
        render: () => (
          <MenuSection
            title={trans('home', {}, 'tools')}
            opened={true}
            toggle={() => true}
            autoClose={props.autoClose}
          />
        )
      }
    ]}
  />

HomeSection.propTypes = {
  homeType: T.string.isRequired,

  // from menu
  autoClose: T.func
}

const HomeActions = (props) =>
  <Toolbar
    id="app-menu-actions"
    className="list-group"
    buttonName="list-group-item"
    actions={[
      {
        name: 'login',
        type: LINK_BUTTON,
        label: trans('login', {}, 'actions'),
        target: '/login',
        displayed: !props.authenticated
      }, {
        name: 'create-account',
        type: LINK_BUTTON,
        label: trans('create-account', {}, 'actions'),
        target: '/registration',
        displayed: props.selfRegistration && !props.authenticated && !props.unavailable
      }
    ]}
    onClick={props.autoClose}
  />

HomeActions.propTypes = {
  unavailable: T.bool.isRequired,
  selfRegistration: T.bool.isRequired,
  authenticated: T.bool.isRequired,

  // from menu
  autoClose: T.func
}

const HomeMenu = (props) =>
  <MenuMain
    title={trans('home')}
  >
    <HomeSection
      homeType={props.homeType}
    />

    <HomeActions
      unavailable={props.unavailable}
      selfRegistration={props.selfRegistration}
      authenticated={props.authenticated}
    />
  </MenuMain>

HomeMenu.propTypes = {
  homeType: T.string.isRequired,
  unavailable: T.bool.isRequired,
  selfRegistration: T.bool.isRequired,
  authenticated: T.bool.isRequired
}

export {
  HomeMenu
}
