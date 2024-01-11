import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/reducer'
import {selectors as formSelectors} from '#/main/app/content/form/store'

import {actions, reducer} from '#/main/privacy/modals/terms-of-service/editor/store'
import {selectors} from '#/main/privacy/administration/privacy/store'
import {EditorModal as EditorModalComponent} from '#/main/privacy/modals/terms-of-service/editor/components/modal'

const EditorModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      formData: formSelectors.data(formSelectors.form(state, selectors.FORM_NAME))
    }),
    (dispatch) => ({
      save(data) {
        dispatch(actions.saveForm(data))
      }
    })
  )(EditorModalComponent)
)

export {
  EditorModal
}
