import {connect} from 'react-redux'

import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'

import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {Editor as EditorComponent} from '#/plugin/drop-zone/resources/dropzone/editor/components/editor'
import {selectors} from '#/plugin/drop-zone/resources/dropzone/store/selectors'

const Editor = connect(
  state => ({
    path: resourceSelectors.path(state),
    workspace: resourceSelectors.workspace(state),
    dropzone: formSelectors.data(formSelectors.form(state, `${selectors.STORE_NAME}.dropzoneForm`))
  }),
  dispatch => ({
    updateProp(propName, propValue) {
      dispatch(formActions.updateProp(`${selectors.STORE_NAME}.dropzoneForm`, propName, propValue))
    }
  })
)(EditorComponent)

export {
  Editor
}
