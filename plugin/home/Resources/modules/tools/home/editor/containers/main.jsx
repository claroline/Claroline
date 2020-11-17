import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {actions as formActions} from '#/main/app/content/form/store'
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
      currentTabIndex: editorSelectors.currentTabIndex(state),
      currentTabTitle: editorSelectors.currentTabTitle(state),
      currentTab: editorSelectors.currentTab(state)
    }),
    (dispatch) => ({
      setCurrentTab(tab){
        dispatch(actions.setCurrentTab(tab))
      },
      createTab(index, tab, navigate) {
        dispatch(editorActions.createTab(index, tab, navigate))
      },
      moveTab(tabId, newPosition) {
        dispatch(editorActions.moveTab(tabId, newPosition))
      },
      updateTab(tabIndex, data, path = null) {
        if (path) {
          dispatch(formActions.updateProp(editorSelectors.FORM_NAME, `[${tabIndex}].${path}`, data))
        } else {
          dispatch(formActions.updateProp(editorSelectors.FORM_NAME, `[${tabIndex}]`, data))
        }
      },
      deleteTab(tabs, currentTab, navigate) {
        let tabIndex = tabs.findIndex(tab => tab.id === currentTab.id)
        tabIndex === 0 ? tabIndex++: tabIndex--

        dispatch(editorActions.deleteTab(tabs, currentTab))
        const redirected = tabs[tabIndex]
        // redirect
        navigate('/edit/' + redirected.slug)
      }
    })
  )(EditorMainComponent)
)

export {
  EditorMain
}
