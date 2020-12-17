import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {PlayerMain} from '#/plugin/home/tools/home/player/containers/main'
import {EditorMain} from '#/plugin/home/tools/home/editor/containers/main'

const HomeTool = props =>
  <Routes
    path={props.path}
    routes={[
      {
        path: '/edit',
        disabled: !props.canEdit,
        component: EditorMain
      }, {
        path: '/',
        component: PlayerMain
      }
    ]}
  />

HomeTool.propTypes = {
  path: T.string.isRequired,
  canEdit: T.bool.isRequired
}

export {
  HomeTool
}
