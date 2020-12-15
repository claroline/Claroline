import {connect} from 'react-redux'

import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'
import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {selectors} from '#/integration/big-blue-button/resources/bbb/store'
import {Editor as EditorComponent} from '#/integration/big-blue-button/resources/bbb/editor/components/editor'

const Editor = connect(
  (state) => ({
    path: resourceSelectors.path(state),
    servers: selectors.servers(state),
    bbb: formSelectors.data(formSelectors.form(state, selectors.STORE_NAME+'.bbbForm')),
    allowRecords: selectors.allowRecords(state)
  }),
  (dispatch) => ({
    updateProp(propName, propValue) {
      dispatch(formActions.updateProp(selectors.STORE_NAME+'.bbbForm', propName, propValue))
    }
  })
)(EditorComponent)

export {
  Editor
}