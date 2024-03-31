import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Tool} from '#/main/core/tool'

import {AppearanceParameters} from '#/main/theme/administration/appearance/containers/parameters'

const AppearanceTool = (props) =>
  <Tool
    {...props}
    styles={['claroline-distribution-main-theme-administration-appearance']}
    pages={[
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
