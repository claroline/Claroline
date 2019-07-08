import {connect} from 'react-redux'
import merge from 'lodash/merge'

import {trans} from '#/main/app/intl/translation'
import {makeId} from '#/main/core/scaffolding/id'
import {actions as formActions} from '#/main/app/content/form/store/actions'
import {selectors as securitySelectors} from '#/main/app/security/store'

import {Tab as TabTypes} from '#/main/core/tools/home/prop-types'

import {EditorMain as EditorMainComponent} from '#/main/core/tools/home/editor/components/main'
import {actions, selectors} from '#/main/core/tools/home/store'
import {actions as editorActions, selectors as editorSelectors} from '#/main/core/tools/home/editor/store'

const EditorMain = connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state),
    currentContext: selectors.context(state),
    administration: selectors.administration(state),
    readOnly: editorSelectors.readOnly(state),
    tabs: editorSelectors.editorTabs(state),
    widgets: editorSelectors.widgets(state),
    currentTabIndex: editorSelectors.currentTabIndex(state),
    currentTabTitle: editorSelectors.currentTabTitle(state),
    currentTab: editorSelectors.currentTab(state)
  }),
  (dispatch, ownProps) => ({
    setCurrentTab(tab){
      dispatch(actions.setCurrentTab(tab))
    },

    updateTab(currentTabIndex, field, value) {
      dispatch(formActions.updateProp(editorSelectors.FORM_NAME, `[${currentTabIndex}].${field}`, value))
    },
    setErrors(errors) {
      dispatch(formActions.setErrors(editorSelectors.FORM_NAME, errors))
    },
    createTab(context, administration, position, navigate){
      const newTabId = makeId()

      dispatch(formActions.updateProp(editorSelectors.FORM_NAME, `[${position}]`, merge({}, TabTypes.defaultProps, {
        id: newTabId,
        title: trans('tab'),
        longTitle: trans('tab'),
        position: position + 1,
        type: administration ? 'administration' : context.type,
        administration: administration,
        user: context.type === 'desktop' && !administration ? ownProps.currentUser : null,
        workspace: context.type === 'workspace' ? {uuid: context.data.uuid} : null
      })))

      // open new tab
      navigate(`${ownProps.path}/edit/tab/${newTabId}`)
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
      navigate(ownProps.path + '/edit/tab/' + redirected.id)
    }
  })
)(EditorMainComponent)

export {
  EditorMain
}
