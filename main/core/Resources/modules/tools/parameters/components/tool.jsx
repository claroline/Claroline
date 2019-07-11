import React from 'react'

import {trans} from '#/main/app/intl/translation'

import {Parameters} from '#/main/core/tools/parameters/components/parameters'
import {PropTypes as T} from 'prop-types'
import {Routes} from '#/main/app/router'
import {ToolPage} from '#/main/core/tool/containers/page'

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
          render: () => {
            const Params = <Parameters/>

            return Params
          }
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
