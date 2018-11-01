import React from 'react'
import {connect} from 'react-redux'

import {withRouter, Routes} from '#/main/app/router'

import {Main} from '#/main/core/administration/parameters/main/components/main'
import {I18n} from '#/main/core/administration/parameters/main/components/i18n'
import {Plugins} from '#/main/core/administration/parameters/main/components/plugins'
import {Portal} from '#/main/core/administration/parameters/main/components/portal'
import {Maintenance} from '#/main/core/administration/parameters/main/components/maintenance'

const SettingsComponent = () =>
  <Routes
    redirect={[
      {from: '/', exact: true, to: '/received' }
    ]}
    routes={[
      {
        path: '/main',
        exact: true,
        component: Main
      },
      {
        path: '/i18n',
        exact: true,
        component: I18n
      }, {
        path: '/plugins',
        exact: true,
        component: Plugins
      }, {
        path: '/portal',
        exact: true,
        component: Portal
      }, {
        path: '/maintenance',
        exact: true,
        component: Maintenance
      }
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
