import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors as formSelectors} from '#/main/app/content/form/store'

import {actions, selectors} from '#/main/community/tools/community/user/store'
import {UserEdit as UserEditComponent} from '#/main/community/tools/community/user/components/edit'

const UserEdit = connect(
  state => ({
    path: toolSelectors.path(state),
    user: formSelectors.data(formSelectors.form(state, selectors.FORM_NAME)),
    username: formSelectors.originalData(formSelectors.form(state, selectors.FORM_NAME)).username
  }),
  (dispatch) => ({
    reload(id) {
      dispatch(actions.open(id, true))
    }
  })
)(UserEditComponent)

export {
  UserEdit
}
