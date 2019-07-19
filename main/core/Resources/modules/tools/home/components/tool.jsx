import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {PlayerMain} from '#/main/core/tools/home/player/containers/main'
import {EditorMain} from '#/main/core/tools/home/editor/containers/main'

const HomeTool = props =>
  <Routes
    path={props.path}
    routes={[
      {
        path: '/edit',
        disabled: !props.editable,
        component: EditorMain
      }, {
        path: '/',
        component: PlayerMain
      }
    ]}
  />

HomeTool.propTypes = {
  path: T.string.isRequired,
  editable: T.bool.isRequired
}

export {
  HomeTool
}
