import {connect} from 'react-redux'

import {actions as formActions, selectors as formSelect} from '#/main/app/content/form/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {selectors as baseSelectors} from '#/main/community/administration/community/store'
import {actions} from '#/main/community/administration/community/role/store'

import {Role as RoleComponent} from '#/main/community/administration/community/role/components/role'

const Role = connect(
  state => ({
    path: toolSelectors.path(state),
    new: formSelect.isNew(formSelect.form(state, baseSelectors.STORE_NAME+'.roles.current')),
    role: formSelect.data(formSelect.form(state, baseSelectors.STORE_NAME+'.roles.current'))
  }),
  dispatch => ({
    loadStatistics(roleId, year) {
      return dispatch(actions.fetchStatistics(roleId, year))
    },
    updateProp(propName, propValue) {
      dispatch(formActions.updateProp(baseSelectors.STORE_NAME+'.roles.current', propName, propValue))
    },
    addUsers(roleId, selected) {
      dispatch(actions.addUsers(roleId, selected.map(row => row.id)))
    },
    addGroups(roleId, selected) {
      dispatch(actions.addGroups(roleId, selected.map(row => row.id)))
    }
  })
)(RoleComponent)

export {
  Role
}
