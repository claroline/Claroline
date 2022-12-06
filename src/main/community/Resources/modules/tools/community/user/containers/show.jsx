import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors as formSelectors} from '#/main/app/content/form/store'

import {UserShow as UserShowComponent} from '#/main/community/tools/community/user/components/show'
import {actions, selectors} from '#/main/community/tools/community/user/store'

const UserShow = connect(
  state => ({
    path: toolSelectors.path(state),
    user: formSelectors.data(formSelectors.form(state, selectors.FORM_NAME))
  }),
  (dispatch) => ({
    reload(id) {
      dispatch(actions.open(id, true))
    },
    addGroups(id, groups) {
      dispatch(actions.addGroups(id, groups))
    },
    addOrganizations(id, organizations) {
      dispatch(actions.addOrganizations(id, organizations))
    },
    addRoles(id, roles) {
      dispatch(actions.addRoles(id, roles))
    }
  })
)(UserShowComponent)

export {
  UserShow
}
