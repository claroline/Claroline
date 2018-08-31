import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {Router, Routes} from '#/main/app/router'

import {Tab as TabTypes} from '#/main/core/tools/home/prop-types'
import {selectors} from '#/main/core/tools/home/selectors'
import {actions} from '#/main/core/tools/home/actions'
import {Player} from '#/main/core/tools/home/player/components/player'
import {Editor} from '#/main/core/tools/home/editor/components/editor'
import {selectors as editorSelectors} from '#/main/core/tools/home/editor/selectors'

const Tool = props =>
  <Router>
    <Routes
      redirect={[
        {from: '/', exact: true, to: '/tab/'+props.tabs[0].id },
        {from: '/edit', exact: true, to: '/edit/tab/'+props.editorTabs[0].id}
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
  tabs: T.arrayOf(T.shape(
    TabTypes.propTypes
  )),
  editorTabs: T.arrayOf(T.shape(
    TabTypes.propTypes
  )),
  currentTab: T.shape(TabTypes.propTypes),
  editable: T.bool.isRequired,
  setCurrentTab: T.func.isRequired
}

const HomeTool = connect(
  (state) => ({
    editable: selectors.editable(state),
    tabs: selectors.tabs(state),
    editorTabs: editorSelectors.editorTabs(state),
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
