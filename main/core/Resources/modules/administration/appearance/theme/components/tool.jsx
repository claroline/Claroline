import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {Router, Routes} from '#/main/core/router'

import {Themes} from '#/main/core/administration/appearance/theme/components/themes.jsx'
import {Theme} from '#/main/core/administration/appearance/theme/components/theme.jsx'
import {actions} from '#/main/core/administration/appearance/theme/actions'

const Tool = props =>
  <Router>
    <Routes
      routes={[
        {
          path: '/',
          component: Themes,
          exact: true
        }, {
          path: '/:id',
          component: Theme,
          onEnter: (params) => props.editTheme(params.id),
          onLeave: props.resetTheme
        }
      ]}
    />
  </Router>

Tool.propTypes = {
  editTheme: T.func.isRequired,
  resetTheme: T.func.isRequired
}

const ThemeTool = connect(
  null,
  dispatch => ({
    editTheme(id) {
      dispatch(actions.editTheme(id))
    },
    resetTheme() {
      dispatch(actions.resetThemeForm())
    }
  })
)(Tool)

export {
  ThemeTool
}
