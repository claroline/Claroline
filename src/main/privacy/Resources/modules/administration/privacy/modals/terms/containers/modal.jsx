import {connect} from 'react-redux'
import {withReducer} from '#/main/app/store/components/withReducer'
import {TermsModal as ThermsModalComponent} from '#/main/privacy/administration/privacy/modals/terms/components/modal'
import {selectors, reducer} from '#/main/privacy/administration/privacy/modals/terms/store'
import {
  actions as formActions,
  selectors as formSelect
} from '#/main/app/content/form/store'

const TermsModal = withReducer(selectors.FORM_NAME, reducer)(
  connect(
    (state) => ({
      formData: formSelect.data(formSelect.form(state, selectors.FORM_NAME)),
      saveEnabled: formSelect.saveEnabled(formSelect.form(state, selectors.FORM_NAME))
    }),
    (dispatch) => ({
      save(formData, onSave) {
        dispatch(formActions.saveForm(selectors.FORM_NAME,
          ['apiv2_privacy_update', {id: formData.id}]))
          .then((response) => {
            onSave(response)}
          )
      }

    })
  )(ThermsModalComponent)
)

export {TermsModal}
