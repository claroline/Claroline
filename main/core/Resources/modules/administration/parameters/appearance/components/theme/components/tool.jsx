import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {Routes} from '#/main/app/router'

import {Themes} from '#/main/core/administration/parameters/appearance/components/theme/components/themes'
import {Theme} from '#/main/core/administration/parameters/appearance/components/theme/components/theme'
import {actions} from '#/main/core/administration/parameters/appearance/components/theme/actions'

const Tool = props =>
  <Routes
    routes={[
      {
        path: '/themes',
        component: Themes,
        exact: true
      }, {
        path: '/themes/:id',
        component: Theme,
        onEnter: (params) => props.editTheme(params.id),
        onLeave: props.resetTheme
      }
    ]}
  />

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
