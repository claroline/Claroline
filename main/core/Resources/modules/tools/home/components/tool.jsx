import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {Router, Routes} from '#/main/app/router'

import {Tab as TabTypes} from '#/main/core/tools/home/prop-types'
import {selectors} from '#/main/core/tools/home/selectors'

import {actions} from '#/main/core/tools/home/actions'
import {Editor} from '#/main/core/tools/home/editor/components/editor'
import {Player} from '#/main/core/tools/home/player/components/player'

const Tool = props =>
  <Router>
    <Routes
      redirect={[
        {from: '/', exact: true, to: '/tab/'+props.sortedTabs[0].id },
        {from: '/edit', exact: true, to: '/edit/tab/'+props.sortedTabs[0].id}
      ]}
      routes={[
        {
          path: '/tab/:id?',
          exact: true,
          component: Player,
          onEnter: (params) =>props.setCurrentTab(params.id)
        }, {
          path: '/edit/tab/:id?',
          component: Editor,
          onEnter: (params) => props.setCurrentTab(params.id),
          disabled: !props.editable
        }
      ]}
    />
  </Router>

Tool.propTypes = {
  context: T.shape({
    type: T.oneOf(['workspace', 'desktop']),
    data: T.shape({
      name: T.string.isRequired
    })
  }),
  sortedTabs: T.arrayOf(T.shape(
    TabTypes.propTypes
  )),
  sortedEditorTabs: T.arrayOf(T.shape(
    TabTypes.propTypes
  )),
  currentTab: T.shape(TabTypes.propTypes),
  editable: T.bool.isRequired,
  setCurrentTab: T.func.isRequired
}

const HomeTool = connect(
  (state) => ({
    editable: selectors.editable(state),
    sortedTabs: selectors.sortedTabs(state),
    currentTab: selectors.currentTab(state)
  }),
  (dispatch) => ({
    setCurrentTab(tab){
      dispatch(actions.setCurrentTab(tab))
    }
  })
)(Tool)

export {
  HomeTool
}
