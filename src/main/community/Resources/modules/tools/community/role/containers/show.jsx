import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors as formSelectors} from '#/main/app/content/form/store'

import {actions, selectors} from '#/main/community/tools/community/role/store'
import {RoleShow as RoleShowComponent} from '#/main/community/tools/community/role/components/show'

const RoleShow = connect(
  state => ({
    path: toolSelectors.path(state),
    role: formSelectors.data(formSelectors.form(state, selectors.FORM_NAME)),
    contextType: toolSelectors.contextType(state),
    contextData: toolSelectors.contextData(state),
    workspaceRights: selectors.workspaceRights(state),
    desktopRights: selectors.desktopRights(state),
    administrationRights: selectors.administrationRights(state)
  }),
  dispatch =>({
    reload(id, contextData) {
      dispatch(actions.open(id, contextData, true))
    },
    loadMetrics(roleId, year) {
      return dispatch(actions.fetchMetrics(roleId, year))
    },

    loadWorkspaceRights(roleId, contextId) {
      return dispatch(actions.fetchWorkspaceRights(roleId, contextId))
    },
    loadDesktopRights(roleId) {
      return dispatch(actions.fetchDesktopRights(roleId))
    },
    loadAdministrationRights(roleId) {
      return dispatch(actions.fetchAdministrationRights(roleId))
    },

    addUsers(roleId, selected) {
      dispatch(actions.addUsers(roleId, selected.map(row => row.id)))
    },
    addGroups(roleId, selected) {
      dispatch(actions.addGroups(roleId, selected.map(row => row.id)))
    }
  })
)(RoleShowComponent)

export {
  RoleShow
}
