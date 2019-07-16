import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {ToolPage} from '#/main/core/tool/containers/page'

import {Parameters} from '#/main/core/tools/parameters/components/parameters'

const ParametersTool = (props) =>
  <ToolPage
    subtitle={
      <Routes
        path={props.path}
        routes={[
          {path: '/', render: () => trans('tools'), exact: true}
        ]}
      />
    }
  >
    <Routes
      path={props.path}
      routes={[
        {
          path: '/',
          component: Parameters
        }]
      }
    />
  </ToolPage>

ParametersTool.propTypes = {
  path: T.string.isRequired
}

export {
  ParametersTool
}
