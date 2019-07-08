import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router/components/routes'

import {LayoutSidebar} from '#/main/app/layout/components/sidebar'
import {LayoutToolbar} from '#/main/app/layout/components/toolbar'
import {HeaderMain} from '#/main/app/layout/header/containers/main'
import {FooterMain} from '#/main/app/layout/footer/containers/main'

import {DesktopMenu} from '#/main/app/layout/sections/desktop/containers/menu'
import {DesktopMain} from '#/main/app/layout/sections/desktop/containers/main'

import {AdministrationMenu} from '#/main/app/layout/sections/administration/containers/menu'
import {AdministrationMain} from '#/main/app/layout/sections/administration/containers/main'

const LayoutMain = props =>
  <Fragment>
    <div className="app" role="presentation">
      <HeaderMain
        maintenance={props.maintenance}
        toggleMenu={props.toggleMenu}
      />

      {props.menuOpened &&
        <Routes
          routes={[
            {
              path: '/',
              exact: true
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
          routes={[
            {
              path: '/',
              exact: true
            }, {
              path: '/desktop',
              component: DesktopMain
            }, {
              path: '/admin',
              component: AdministrationMain
            }
          ]}
        />

        <FooterMain />
      </div>

      <LayoutToolbar
        opened={props.sidebar}
        open={props.openSidebar}
      />
    </div>

    {props.sidebar &&
      <LayoutSidebar
        close={props.closeSidebar}
      />
    }
  </Fragment>

LayoutMain.propTypes = {
  maintenance: T.bool.isRequired,

  menuOpened: T.bool.isRequired,
  toggleMenu: T.func.isRequired,

  sidebar: T.string,
  openSidebar: T.func.isRequired,
  closeSidebar: T.func.isRequired
}

export {
  LayoutMain
}
