import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'
import {AppearanceParameters} from '#/main/theme/administration/appearance/containers/parameters'

const AppearanceTool = (props) =>
  <Routes
    path={props.path}
    routes={[
      {
        path: '/',
        exact: true,
        component: AppearanceParameters
      }
    ]}
  />

AppearanceTool.propTypes = {
  path: T.string.isRequired
}

export {
  AppearanceTool
}
