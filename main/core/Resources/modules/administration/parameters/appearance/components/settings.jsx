import React from 'react'
import {connect} from 'react-redux'

import {withRouter, Routes} from '#/main/app/router'

import {Footer} from '#/main/core/administration/parameters/appearance/components/footer'
import {Header} from '#/main/core/administration/parameters/appearance/components/header'
import {Icons} from '#/main/core/administration/parameters/appearance/components/icons'
//import {ThemeTool as Themes} from '#/main/core/administration/parameters/appearance/components/theme/components/tool'


const SettingsComponent = () =>
  <Routes
    redirect={[
      {from: '/', exact: true, to: '/main' }
    ]}
    routes={[
      {
        path: '/header',
        exact: true,
        component: Header
      },
      {
        path: '/footer',
        exact: true,
        component: Footer
      },
      {
        path: '/icons',
        exact: true,
        component: Icons
      }/*, {
        path: '/themes',
        exact: true,
        component: Themes
      }*/
    ]}
  />

SettingsComponent.propTypes = {
}

const Settings = withRouter(connect(
  () => ({ }),
  () => ({ })
)(SettingsComponent))


export {
  Settings
}
