import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {HomeEditor as HomeEditorComponent} from '#/plugin/home/tools/home/editor/components/main'
import {actions} from '#/plugin/home/tools/home/store'
import {actions as editorActions, selectors as editorSelectors} from '#/plugin/home/tools/home/editor/store'
import {selectors as playerSelectors} from '#/plugin/home/tools/home/player/store'
import {actions as formActions} from '#/main/app/content/form/store'
import {selectors as parametersSelectors} from '#/main/core/tool/editor/store'

const HomeEditor = withRouter(
  connect(
    (state) => ({
      path: toolSelectors.path(state),
      loaded: toolSelectors.loaded(state),
      currentContext: toolSelectors.context(state),
      contextType: toolSelectors.contextType(state),
      contextId: toolSelectors.contextId(state),

      tabs: playerSelectors.tabs(state),
      editorTabs: editorSelectors.editorTabs(state),
      currentTabTitle: editorSelectors.currentTabTitle(state),
      currentTab: editorSelectors.currentTab(state)
    }),
    (dispatch) => ({
      load(tabs) {
        dispatch(formActions.load(parametersSelectors.STORE_NAME, {tabs: tabs}))
      },
      setCurrentTab(tab){
        dispatch(actions.setCurrentTab(tab))
      },
      createTab(parent = null, tab, navigate) {
        dispatch(editorActions.createTab(parent, tab, navigate))
      },
      moveTab(tabId, newPosition) {
        dispatch(editorActions.moveTab(tabId, newPosition))
      },
      updateTab(tabs, tabId, data, path = null) {
        dispatch(editorActions.updateTab(tabs, tabId, data, path))
      },
      deleteTab(tabs, currentTab) {
        dispatch(editorActions.deleteTab(tabs, currentTab))
      }
    })
  )(HomeEditorComponent)
)

export {
  HomeEditor
}
