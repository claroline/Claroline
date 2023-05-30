import {connect} from 'react-redux'
import {withReducer} from '#/main/app/store/components/withReducer'
import {CountryModal as CountryModalComponent} from '#/main/privacy/administration/privacy/modals/country/components/modal'
import {selectors, reducer} from '#/main/privacy/administration/privacy/store'
import {
  actions as formActions,
  selectors as formSelect
} from '#/main/app/content/form/store'

const CountryModal = withReducer(selectors.FORM_NAME, reducer)(
  connect(
    (state) => ({
      formData: formSelect.data(formSelect.form(state, selectors.FORM_NAME)),
      saveEnabled: formSelect.saveEnabled(formSelect.form(state, selectors.FORM_NAME))
    }),
    (dispatch) => ({
      save(formData, onSave) {
        dispatch(formActions.saveForm(selectors.STORE_NAME, formData.id ? ['apiv2_example_update', {id: formData.id}] : ['apiv2_example_create'])).then((response) => {
          onSave(response)
        })
      },
      reset() {
        dispatch(formActions.resetForm(selectors.STORE_NAME, null, true))
      }
    })
  )(CountryModalComponent)
)

export {CountryModal}
