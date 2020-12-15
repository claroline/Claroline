import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

// TODO : make it dynamic
import {ExternalTool} from '#/main/core/tools/parameters/external/components/tool'
import {TokensTool} from '#/main/core/tools/parameters/tokens/containers/tool'

const ParametersTool = (props) =>
  <Routes
    path={props.path}
    routes={[
      {
        path: '/external',
        component: ExternalTool,
        disabled: true
      }, {
        path: '/tokens',
        component: TokensTool
      }
    ]}
  />

ParametersTool.propTypes = {
  path: T.string.isRequired
}

export {
  ParametersTool
}
