import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'

import {AddDocumentModal as AddDocumentModalComponent} from '#/plugin/drop-zone/resources/dropzone/player/modals/document/components/modal'
import {selectors, reducer} from '#/plugin/drop-zone/resources/dropzone/player/modals/document/store'

const AddDocumentModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      data: formSelectors.data(formSelectors.form(state, selectors.STORE_NAME)),
      saveEnabled: formSelectors.data(formSelectors.form(state, selectors.STORE_NAME))
    }),
    (dispatch) => ({
      resetForm(formData) {
        dispatch(formActions.resetForm(selectors.STORE_NAME, formData))
      }
    })
  )(AddDocumentModalComponent)
)

export {
  AddDocumentModal
}
