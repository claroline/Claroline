import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {ProgressionShow} from '#/main/evaluation/tools/progression/components/show'

const ProgressionTool = props =>
  <Routes
    path={props.path}
    routes={[
      {
        path: '',
        exact: true,
        component: ProgressionShow
      }
    ]}
  />

ProgressionTool.propTypes = {
  path: T.string.isRequired
}

export {
  ProgressionTool
}
