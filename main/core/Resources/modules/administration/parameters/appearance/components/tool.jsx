import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {ToolPage} from '#/main/core/tool/containers/page'

import {Layout} from '#/main/core/administration/parameters/appearance/components/layout'
import {Icons} from '#/main/core/administration/parameters/appearance/components/icons'

const AppearanceTool = (props) =>
  <ToolPage
    subtitle={
      <Routes
        path={props.path}
        routes={[
          {path: '/layout', render: () => trans('layout')},
          {path: '/icons',  render: () => trans('icons')}
        ]}
      />
    }
  >
    <Routes
      path={props.path}
      redirect={[
        {from: '/', exact: true, to: '/layout' }
      ]}
      routes={[
        {
          path: '/layout',
          component: Layout
        }, {
          path: '/icons',
          component: Icons
        }
      ]}
    />
  </ToolPage>

AppearanceTool.propTypes = {
  path: T.string.isRequired
}

export {
  AppearanceTool
}
