import {connect} from 'react-redux'
import {withReducer} from '#/main/app/store/components/withReducer'
import {ThermsModal as ThermsModalComponent} from '#/main/privacy/administration/privacy/modals/therms/components/modal'
import {selectors, reducer} from '#/main/privacy/administration/privacy/store'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'

const ThermsModal = withReducer(selectors.FORM_NAME, reducer)(
  connect(
    (state) => ({
      formData:
        formSelectors.data(formSelectors.form(state, selectors.FORM_NAME)),
      saveEnabled: formSelectors.saveEnabled(formSelectors.form(state, selectors.FORM_NAME))
    }),
    (dispatch) => ({
      save(formData, onSave) {
        dispatch(formActions.saveForm(selectors.FORM_NAME,
          ['apiv2_privacy_therms_update', {id: formData.id}].then((response) => {
            onSave(response)}
          )))
      }
    })
  )(ThermsModalComponent)
)

export {ThermsModal}
