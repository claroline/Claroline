import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors as formSelectors} from '#/main/app/content/form/store'

import {actions, selectors} from '#/main/community/tools/community/group/store'
import {GroupEdit as GroupEditComponent} from '#/main/community/tools/community/group/components/edit'

const GroupEdit = connect(
  state => ({
    path: toolSelectors.path(state),
    group: formSelectors.data(formSelectors.form(state, selectors.FORM_NAME))
  }),
  (dispatch) => ({
    reload(id) {
      dispatch(actions.open(id, true))
    }
  })
)(GroupEditComponent)

export {
  GroupEdit
}
