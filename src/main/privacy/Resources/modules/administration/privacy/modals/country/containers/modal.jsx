import {connect} from 'react-redux'
import {withReducer} from '#/main/app/store/components/withReducer'
import {CountryModal as CountryModalComponent} from '#/main/privacy/administration/privacy/modals/country/components/modal'
import {selectors, reducer} from '#/main/privacy/administration/privacy/modals/country/store'
import {
  actions as formActions,
  selectors as formSelect
} from '#/main/app/content/form/store'

const CountryModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      formData: formSelect.data(formSelect.form(state, selectors.STORE_NAME)),
      saveEnabled: formSelect.saveEnabled(formSelect.form(state, selectors.STORE_NAME))
    }),
    (dispatch) => ({
      save(formData, onSave) {
        dispatch(formActions.saveForm(selectors.STORE_NAME,
          ['apiv2_privacy_update', {id: formData.id}]))
          .then((response) => {
            onSave(response)}
          )
      }

    })
  )(CountryModalComponent)
)

export {CountryModal}
