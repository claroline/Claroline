import {connect} from 'react-redux'
import {withReducer} from '#/main/app/store/components/withReducer'
import {CountryModal as CountryModalComponent} from '#/main/privacy/administration/privacy/modals/country/components/modal'
import {selectors, reducer} from '#/main/privacy/administration/privacy/modals/country/store'
import {
  actions as formActions,
  selectors as formSelect
} from '#/main/app/content/form/store'

const CountryModal = withReducer(selectors.FORM_NAME, reducer)(
  connect(
    (state) => ({
      parameters: selectors.store(state),
      formData: formSelect.data(formSelect.form(state, selectors.FORM_NAME)),
      saveEnabled: formSelect.saveEnabled(formSelect.form(state, selectors.FORM_NAME))
    }),
    (dispatch) => ({
      save(formData, onSave) {
        dispatch(formActions.saveForm(selectors.FORM_NAME, formData.id ? ['apiv2_privacy_update', {id: formData.id}] : ['apiv2_privacy_create'])).then((response) => {
          onSave(response)
        })
      },
      reset() {
        dispatch(formActions.resetForm(selectors.FORM_NAME, null, true))
      }
    })
  )(CountryModalComponent)
)

export {CountryModal}
