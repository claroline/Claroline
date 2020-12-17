import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {EditorMenu as EditorMenuComponent} from '#/plugin/home/tools/home/editor/components/menu'
import {actions, selectors} from '#/plugin/home/tools/home/editor/store'
import {selectors as homeSelectors} from '#/plugin/home/tools/home/store'

const EditorMenu = withRouter(
  connect(
    (state) => ({
      path: toolSelectors.path(state),
      currentContext: toolSelectors.context(state),
      currentUser: securitySelectors.currentUser(state),
      tabs: selectors.editorTabs(state),
      administration: homeSelectors.administration(state)
    }),
    (dispatch) => ({
      createTab(parent = null, tab, navigate) {
        dispatch(actions.createTab(parent, tab, navigate))
      },
      moveTab(tabId, newPosition) {
        dispatch(actions.moveTab(tabId, newPosition))
      },
      updateTab(tabs, tabId, data, path = null) {
        dispatch(actions.updateTab(tabs, tabId, data, path))
      },
      deleteTab(tabs, currentTab) {
        dispatch(actions.deleteTab(tabs, currentTab))
      }
    })
  )(EditorMenuComponent)
)

export {
  EditorMenu
}
