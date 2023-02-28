import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router/components/routes'

import {HeaderMain} from '#/main/app/layout/header/containers/main'
import {FooterMain} from '#/main/app/layout/footer/containers/main'

import {HomeMain} from '#/main/app/layout/sections/home/containers/main'
import {HomeMenu} from '#/main/app/layout/sections/home/containers/menu'

import {DesktopMenu} from '#/main/app/layout/sections/desktop/containers/menu'
import {DesktopMain} from '#/main/app/layout/sections/desktop/containers/main'

import {AdministrationMenu} from '#/main/app/layout/sections/administration/containers/menu'
import {AdministrationMain} from '#/main/app/layout/sections/administration/containers/main'

import {AccountMenu} from '#/main/app/layout/sections/account/containers/menu'
import {AccountMain} from '#/main/app/layout/sections/account/components/main'

import {WorkspaceMenu} from '#/main/core/workspace/containers/menu'
import {WorkspaceMain} from '#/main/core/workspace/containers/main'

const LayoutMain = props =>
  <Fragment>
    <div className="app" role="presentation">
      {false && <div className="app-loader" />}

      <HeaderMain
        unavailable={props.unavailable}
        toggleMenu={props.toggleMenu}
      />

      {props.menuOpened &&
        <Routes
          redirect={[
            {from: '/desktop', to: '/', disabled: !props.unavailable},
            {from: '/admin',   to: '/', disabled: !props.unavailable}
          ]}
          routes={[
            {
              path: '/desktop/workspaces/open/:slug',
              component: WorkspaceMenu
            }, {
              path: '/desktop',
              component: DesktopMenu,
              disabled: props.unavailable
            }, {
              path: '/admin',
              component: AdministrationMenu,
              disabled: props.unavailable
            }, {
              path: '/account',
              component: AccountMenu,
              disabled: !props.authenticated
            },
            // it must be declared last otherwise it will always match.
            // and it cannot be set to exact: true because it contains sub routes for maintenance, login and registration.
            {
              path: '/',
              component: HomeMenu
            }
          ]}
        />
      }

      <div className="app-content" role="presentation">
        <Routes
          redirect={[
            {from: '/desktop', to: '/', disabled: !props.unavailable},
            {from: '/admin',   to: '/', disabled: !props.unavailable}
          ]}
          routes={[
            {
              path: '/desktop/workspaces/open/:slug',
              onEnter: (params = {}) => props.openWorkspace(params.slug),
              component: WorkspaceMain,
              disabled: props.unavailable
            }, {
              path: '/desktop',
              component: DesktopMain,
              disabled: props.unavailable
            }, {
              path: '/admin',
              component: AdministrationMain,
              disabled: props.unavailable
            }, {
              path: '/account',
              component: AccountMain,
              disabled: !props.authenticated
            },
            // it must be declared last otherwise it will always match.
            // and it cannot be set to exact: true because it contains sub routes for maintenance, login and registration.
            {
              path: '/',
              component: HomeMain
            }
          ]}
        />

        <FooterMain />
      </div>
    </div>
  </Fragment>

LayoutMain.propTypes = {
  unavailable: T.bool.isRequired,
  authenticated: T.bool.isRequired,

  openWorkspace: T.func.isRequired,

  menuOpened: T.bool.isRequired,
  toggleMenu: T.func.isRequired
}

export {
  LayoutMain
}
