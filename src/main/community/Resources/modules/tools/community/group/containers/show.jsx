import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors as formSelectors} from '#/main/app/content/form/store'

import {actions, selectors} from '#/main/community/tools/community/group/store'
import {GroupShow as GroupShowComponent} from '#/main/community/tools/community/group/components/show'

const GroupShow = connect(
  state => ({
    path: toolSelectors.path(state),
    contextType: toolSelectors.contextType(state),
    group: formSelectors.data(formSelectors.form(state, selectors.FORM_NAME))
  }),
  dispatch =>({
    reload(id) {
      dispatch(actions.open(id, true))
    },
    addUsers(groupId, selected) {
      dispatch(actions.addUsers(groupId, selected.map(row => row.id)))
    },
    addRoles(groupId, selected) {
      dispatch(actions.addRoles(groupId, selected.map(row => row.id)))
    },
    addOrganizations(groupId, selected) {
      dispatch(actions.addOrganizations(groupId, selected.map(row => row.id)))
    }
  })
)(GroupShowComponent)

export {
  GroupShow
}
