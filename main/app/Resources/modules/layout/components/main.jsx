import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router/components/routes'

import {LayoutSidebar} from '#/main/app/layout/components/sidebar'
import {LayoutToolbar} from '#/main/app/layout/components/toolbar'
import {HeaderMain} from '#/main/app/layout/header/containers/main'
import {FooterMain} from '#/main/app/layout/footer/containers/main'

import {HomeMain} from '#/main/app/layout/sections/home/containers/main'

import {DesktopMenu} from '#/main/app/layout/sections/desktop/containers/menu'
import {DesktopMain} from '#/main/app/layout/sections/desktop/containers/main'

import {AdministrationMenu} from '#/main/app/layout/sections/administration/containers/menu'
import {AdministrationMain} from '#/main/app/layout/sections/administration/containers/main'

import {WorkspaceMenu} from '#/main/core/workspace/containers/menu'
import {WorkspaceMain} from '#/main/core/workspace/containers/main'

const LayoutMain = props =>
  <Fragment>
    <div className="app" role="presentation">
      {false &&
        <div className="app-loader" />
      }

      <HeaderMain
        maintenance={props.maintenance}
        toggleMenu={props.toggleMenu}
      />

      {props.menuOpened &&
        <Routes
          routes={[
            {
              path: '/desktop/workspaces/open/:slug',
              component: WorkspaceMenu
            }, {
              path: '/desktop',
              component: DesktopMenu
            }, {
              path: '/admin',
              component: AdministrationMenu
            }
          ]}
        />
      }

      <div className="app-content" role="presentation">
        <Routes
          redirect={[
            {from: '/desktop', to: '/', disabled: !props.maintenance || props.authenticated},
            {from: '/admin',   to: '/', disabled: !props.maintenance || props.authenticated}
          ]}
          routes={[
            {
              path: '/desktop/workspaces/open/:slug',
              onEnter: (params = {}) => props.openWorkspace(params.slug),
              component: WorkspaceMain
            }, {
              path: '/desktop',
              component: DesktopMain,
              disabled: !props.authenticated && props.maintenance
            }, {
              path: '/admin',
              component: AdministrationMain,
              disabled: !props.authenticated && props.maintenance
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

      {false && props.authenticated &&
        <LayoutToolbar
          opened={props.sidebar}
          open={props.openSidebar}
        />
      }
    </div>

    {false && (props.authenticated && props.sidebar) &&
      <LayoutSidebar
        close={props.closeSidebar}
      />
    }
  </Fragment>

LayoutMain.propTypes = {
  maintenance: T.bool.isRequired,
  authenticated: T.bool.isRequired,

  openWorkspace: T.func.isRequired,

  menuOpened: T.bool.isRequired,
  toggleMenu: T.func.isRequired,

  sidebar: T.string,
  openSidebar: T.func.isRequired,
  closeSidebar: T.func.isRequired
}

export {
  LayoutMain
}
