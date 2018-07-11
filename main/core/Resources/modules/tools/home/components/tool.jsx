import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {Router, Routes} from '#/main/app/router'
import {matchPath, withRouter} from '#/main/app/router'
import {FormPageActionsContainer} from '#/main/core/data/form/containers/page-actions.jsx'
import {actions as formActions} from '#/main/core/data/form/actions'
import {PageActions} from '#/main/core/layout/page'

import {Tab as TabTypes} from '#/main/core/tools/home/prop-types'
import {select} from '#/main/core/tools/home/selectors'
import {actions} from '#/main/core/tools/home/actions'
import {Editor} from '#/main/core/tools/home/editor/components/editor'
import {Player} from '#/main/core/tools/home/player/components/player'

const ToolActionsComponent = props =>

  <PageActions>
    <FormPageActionsContainer
      formName="editor"
      target={['apiv2_home_update']}
      opened={!!matchPath(props.location.pathname, {path: '/edit'})}
      open={{
        type: 'link',
        target: '/edit'
      }}
      cancel={{
        type: 'link',
        target: '/',
        exact: true
      }}
    />
  </PageActions>

ToolActionsComponent.propTypes = {
  location: T.shape({
    pathname: T.string
  }).isRequired
}

const ToolActions = withRouter(ToolActionsComponent)

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
          exact: true,
          component: Editor,
          onEnter: (params) => {
            props.setCurrentTab(params.id)
            props.resetForm(props.sortedTabs)
          },
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
  currentTab: T.shape(TabTypes.propTypes),
  editable: T.bool.isRequired,
  setCurrentTab: T.func.isRequired,
  resetForm: T.func.isRequired
}

const HomeTool = connect(
  (state) => ({
    editable: select.editable(state),
    sortedTabs: select.sortedTabs(state),
    currentTab: select.currentTab(state)
  }),
  (dispatch) => ({
    setCurrentTab(tab){
      dispatch(actions.setCurrentTab(tab))
    },
    resetForm(tabs){
      dispatch(formActions.resetForm('editor', tabs))
    }

  })
)(Tool)

export {
  HomeTool,
  ToolActions
}
