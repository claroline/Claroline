import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'

import {ToolPage} from '#/main/core/tool/containers/page'
import {Meta} from '#/main/core/administration/parameters/main/containers/meta'
import {Technical} from '#/main/core/administration/parameters/technical/containers/technical'

import {AppearanceTool} from '#/main/theme/administration/appearance/containers/tool'

const ParametersTool = (props) =>
  <ToolPage
    className="main-settings-container"
    subtitle={
      <Routes
        path={props.path}
        routes={[
          {path: '/', exact: true, render: () => trans('general')},
          {path: '/technical',     render: () => trans('technical')},
          {path: '/appearance',    render: () => trans('appearance')}
        ]}
      />
    }
  >
    <Routes
      path={props.path}
      routes={[
        {
          path: '/',
          exact: true,
          component: Meta
        },
        {
          path: '/technical',
          component: Technical
        }, {
          path: '/appearance',
          component: AppearanceTool
        }
      ]}
    />
  </ToolPage>

ParametersTool.propTypes = {
  path: T.string,
  location: T.shape({
    pathname: T.string
  })
}

export {
  ParametersTool
}
