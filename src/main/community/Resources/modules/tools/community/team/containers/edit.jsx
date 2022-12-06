import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors as formSelectors} from '#/main/app/content/form/store'

import {actions, selectors} from '#/main/community/tools/community/team/store'
import {TeamEdit as TeamEditComponent} from '#/main/community/tools/community/team/components/edit'

const TeamEdit = connect(
  state => ({
    path: toolSelectors.path(state),
    team: formSelectors.data(formSelectors.form(state, selectors.FORM_NAME))
  }),
  (dispatch) => ({
    reload(id) {
      dispatch(actions.open(id, true))
    }
  })
)(TeamEditComponent)

export {
  TeamEdit
}
