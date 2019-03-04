import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {Tab as TabTypes} from '#/main/core/tools/home/prop-types'
import {Player} from '#/main/core/tools/home/player/components/player'
import {Editor} from '#/main/core/tools/home/editor/components/editor'

const HomeTool = props =>
  <Routes
    redirect={[
      {from: '/', exact: true, to: '/tab/'+props.sortedTabs[0].id },
      {from: '/edit', exact: true, to: '/edit/tab/'+props.editorTabs[0].id}
    ]}
    routes={[
      {
        path: '/tab/:id?',
        exact: true,
        component: Player,
        onEnter: (params) => props.setCurrentTab(params.id)
      }, {
        path: '/edit/tab/:id?',
        component: Editor,
        onEnter: (params) => {
          props.setCurrentTab(params.id)
        },
        disabled: !props.editable
      }
    ]}
  />

HomeTool.propTypes = {
  sortedTabs: T.arrayOf(T.shape(
    TabTypes.propTypes
  )),
  editorTabs: T.arrayOf(T.shape(
    TabTypes.propTypes
  )),
  currentTab: T.shape(TabTypes.propTypes),
  editable: T.bool.isRequired,
  setCurrentTab: T.func.isRequired
}

export {
  HomeTool
}
