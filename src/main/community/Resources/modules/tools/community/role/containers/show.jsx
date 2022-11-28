import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors as formSelectors} from '#/main/app/content/form/store'

import {actions, selectors} from '#/main/community/tools/community/role/store'
import {RoleShow as RoleShowComponent} from '#/main/community/tools/community/role/components/show'

const RoleShow = connect(
  state => ({
    path: toolSelectors.path(state),
    role: formSelectors.data(formSelectors.form(state, selectors.FORM_NAME))
  }),
  dispatch =>({
    reload(id) {
      dispatch(actions.open(id, true))
    },
    loadMetrics(roleId, year) {
      return dispatch(actions.fetchMetrics(roleId, year))
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
