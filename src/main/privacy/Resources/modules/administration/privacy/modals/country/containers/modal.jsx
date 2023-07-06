import {connect} from 'react-redux'
import {withReducer} from '#/main/app/store/components/withReducer'
import {CountryModal as CountryModalComponent} from '#/main/privacy/administration/privacy/modals/country/components/modal'
import {selectors, reducer} from '#/main/privacy/administration/privacy/modals/country/store'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'

const CountryModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      formData: formSelectors.data(formSelectors.form(state, selectors.STORE_NAME)),
      saveEnabled: formSelectors.saveEnabled(formSelectors.form(state, selectors.STORE_NAME))
    }),
    (dispatch) => ({
      save(formData, onSave) {
        dispatch(formActions.saveForm(selectors.STORE_NAME,
          ['apiv2_privacy_update'])).then((response) => {
          onSave(response)}
        )
      },
      reset(countryStorage) {
        dispatch(formActions.resetForm(selectors.STORE_NAME, {'countryStorage':countryStorage}, false))
      }
    })
  )(CountryModalComponent)
)

export {CountryModal}
