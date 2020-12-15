import {connect} from 'react-redux'

import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {selectors} from '#/plugin/team/tools/team/store'
import {Editor as EditorComponent} from '#/plugin/team/tools/team/components/editor'

const Editor = connect(
  (state) => ({
    path: toolSelectors.path(state),
    teamParams: formSelectors.data(formSelectors.form(state, selectors.STORE_NAME + '.teamParamsForm'))
  }),
  (dispatch) => ({
    saveForm(id) {
      dispatch(formActions.saveForm(selectors.STORE_NAME + '.teamParamsForm', ['apiv2_workspaceteamparameters_update', {id: id}]))
    }
  })
)(EditorComponent)

export {
  Editor
}