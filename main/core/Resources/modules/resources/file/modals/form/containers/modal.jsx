import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {
  actions as formActions,
  selectors as formSelect
} from '#/main/app/content/form/store'

import {FileFormModal as FileFormModalComponent} from '#/main/core/resources/file/modals/form/components/modal'
import {actions, reducer, selectors} from '#/main/core/resources/file/modals/form/store'

const FileFormModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      data: formSelect.data(formSelect.form(state, selectors.STORE_NAME)),
      saveEnabled: formSelect.saveEnabled(formSelect.form(state, selectors.STORE_NAME))
    }),
    (dispatch) => ({
      resetForm() {
        dispatch(formActions.reset(selectors.STORE_NAME, {}))
      },
      save(node, file, onChange) {
        dispatch(actions.changeFile(node, file)).then((response) => {
          if (onChange) {
            onChange(response)
          }
        })
      }
    })
  )(FileFormModalComponent)
)

export {
  FileFormModal
}
