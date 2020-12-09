import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {EditorMain as EditorMainComponent} from '#/plugin/home/tools/home/editor/components/main'
import {actions, selectors} from '#/plugin/home/tools/home/store'
import {actions as editorActions, selectors as editorSelectors} from '#/plugin/home/tools/home/editor/store'

const EditorMain = withRouter(
  connect(
    (state) => ({
      path: toolSelectors.path(state),
      currentContext: toolSelectors.context(state),
      currentUser: securitySelectors.currentUser(state),
      administration: selectors.administration(state),

      readOnly: editorSelectors.readOnly(state),
      tabs: editorSelectors.editorTabs(state),
      currentTabTitle: editorSelectors.currentTabTitle(state),
      currentTab: editorSelectors.currentTab(state)
    }),
    (dispatch) => ({
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
  )(EditorMainComponent)
)

export {
  EditorMain
}
