import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {ThemeList} from '#/main/theme/administration/appearance/theme'

const ThemeMain = (props) =>
  <Routes
    path={`${props.path}/themes`}
    routes={[
      {
        path: '/',
        exact: true,
        render: () => (
          <ThemeList path={props.path} />
        )
      }
    ]}
  />

ThemeMain.propTypes = {
  path: T.string.isRequired
}

export {
  ThemeMain
}