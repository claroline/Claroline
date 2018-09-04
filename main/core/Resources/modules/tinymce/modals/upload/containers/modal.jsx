import {connect} from 'react-redux'
import {withReducer} from '#/main/app/store/components/withReducer'

import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'

import {UploadModal as UploadModalComponent} from '#/main/core/tinymce/modals/upload/components/modal'
import {actions, reducer, selectors} from '#/main/core/tinymce/modals/upload/store'

const UploadModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      formName: selectors.FORM_NAME,
      uploadEnabled: formSelectors.saveEnabled(formSelectors.form(state, selectors.FORM_NAME)),
      uploadDestinations: selectors.uploadDestinations(state)
    }),
    (dispatch) => ({
      fetchUploadDestinations() {
        dispatch(actions.fetchUploadDestinations())
      },
      upload(onSuccess) {
        dispatch(formActions.save(selectors.FORM_NAME, ['claro_tinymce_file_upload'])).then((resourceNode) => onSuccess(resourceNode))
      }
    })
  )(UploadModalComponent)
)

export {
  UploadModal
}
