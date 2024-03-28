import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'
import {AppearanceParameters} from '#/main/theme/administration/appearance/containers/parameters'
import {Tool} from '#/main/core/tool'

const AppearanceTool = (props) =>
  <Tool
    {...props}
    styles={['claroline-distribution-main-theme-administration-appearance']}
  >
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
  </Tool>

AppearanceTool.propTypes = {
  path: T.string.isRequired
}

export {
  AppearanceTool
}
