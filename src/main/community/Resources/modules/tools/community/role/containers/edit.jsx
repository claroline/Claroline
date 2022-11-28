import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors as formSelectors} from '#/main/app/content/form/store'

import {actions, selectors} from '#/main/community/tools/community/role/store'
import {RoleEdit as RoleEditComponent} from '#/main/community/tools/community/role/components/edit'

const RoleEdit = connect(
  state => ({
    path: toolSelectors.path(state),
    role: formSelectors.data(formSelectors.form(state, selectors.FORM_NAME))
  }),
  (dispatch) => ({
    reload(id) {
      dispatch(actions.open(id, true))
    }
  })
)(RoleEditComponent)

export {
  RoleEdit
}
