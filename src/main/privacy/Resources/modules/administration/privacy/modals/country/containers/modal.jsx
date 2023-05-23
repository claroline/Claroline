import {connect} from 'react-redux'
import {withReducer} from '#/main/app/store/components/withReducer'
import {CountryModal as CountryModalComponent} from '#/main/privacy/administration/privacy/modals/country/components/modal'
import {selectors, reducer} from '#/main/privacy/administration/privacy/modals/country/store'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'

const CountryModal = withReducer(selectors.FORM_NAME, reducer)(
  connect(
    (state) => ({
      formData:
        formSelectors.data(formSelectors.form(state, selectors.FORM_NAME)),
      saveEnabled: formSelectors.saveEnabled(formSelectors.form(state, selectors.FORM_NAME))
    }),
    (dispatch) => ({
      save(formData, onSave) {
        dispatch(formActions.saveForm(selectors.FORM_NAME,
          ['apiv2_privacy_country_storage_update', {id: formData.id}]))
          .then((response) => {
            onSave(response)}
          )
      }

    })
  )(CountryModalComponent)
)

export {CountryModal}
