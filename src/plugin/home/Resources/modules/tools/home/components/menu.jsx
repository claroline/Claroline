import React from 'react'
import {PropTypes as T} from 'prop-types'
import {Routes} from '#/main/app/router'

import {EditorMenu} from '#/plugin/home/tools/home/editor/containers/menu'
import {PlayerMenu} from '#/plugin/home/tools/home/player/containers/menu'

const HomeMenu = props =>
  <Routes
    path={props.path}
    routes={[
      {
        path: '/edit',
        disabled: !props.canEdit,
        component: EditorMenu
      }, {
        path: '/',
        component: PlayerMenu
      }
    ]}
  />

HomeMenu.propTypes = {
  path: T.string.isRequired,
  canEdit: T.bool.isRequired
}

export {
  HomeMenu
}
