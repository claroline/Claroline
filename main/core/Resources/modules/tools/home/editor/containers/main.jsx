import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {actions as formActions} from '#/main/app/content/form/store/actions'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {EditorMain as EditorMainComponent} from '#/main/core/tools/home/editor/components/main'
import {actions, selectors} from '#/main/core/tools/home/store'
import {actions as editorActions, selectors as editorSelectors} from '#/main/core/tools/home/editor/store'

const EditorMain = withRouter(
  connect(
    (state) => ({
      path: toolSelectors.path(state),
      currentUser: securitySelectors.currentUser(state),
      currentContext: selectors.context(state),
      administration: selectors.administration(state),
      readOnly: editorSelectors.readOnly(state),
      tabs: editorSelectors.editorTabs(state),
      roles: selectors.roles(state),
      widgets: editorSelectors.widgets(state),
      currentTabIndex: editorSelectors.currentTabIndex(state),
      currentTabTitle: editorSelectors.currentTabTitle(state),
      currentTab: editorSelectors.currentTab(state)
    }),
    (dispatch) => ({
      setCurrentTab(tab){
        dispatch(actions.setCurrentTab(tab))
      },
      updateTab(currentTabIndex, field, value) {
        dispatch(formActions.updateProp(editorSelectors.FORM_NAME, `[${currentTabIndex}].${field}`, value))
      },
      setErrors(errors) {
        dispatch(formActions.setErrors(editorSelectors.FORM_NAME, errors))
      },
      createTab(context, administration, currentUser, position, navigate) {
        dispatch(editorActions.createTab(context, administration, position, currentUser, navigate))
      },
      moveTab(tabs, currentTab, newPosition) {
        dispatch(editorActions.moveTab(tabs, currentTab, newPosition))
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
