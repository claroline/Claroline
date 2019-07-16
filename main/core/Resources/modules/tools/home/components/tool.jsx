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
        path: '/tab',
        component: PlayerMain
      }, {
        path: '/edit/tab',
        disabled: !props.editable,
        component: EditorMain
      }
    ]}
    redirect={[
      {from: '/', exact: true, to: '/tab'}
    ]}
  />

HomeTool.propTypes = {
  path: T.string.isRequired,
  editable: T.bool.isRequired
}

export {
  HomeTool
}
