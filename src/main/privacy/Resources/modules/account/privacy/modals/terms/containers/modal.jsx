import {connect} from 'react-redux'
import {withReducer} from '#/main/app/store/components/withReducer'
import {TermsModal as ThermsModalComponent} from '#/main/privacy/account/privacy/modals/terms/components/modal'
import {selectors, reducer} from '#/main/privacy/account/privacy/modals/terms/store'
import {
  actions as formActions,
  selectors as formSelectors
} from '#/main/app/content/form/store'

const TermsModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      parameters: selectors.store(state),
      formData: formSelectors.data(formSelectors.form(state, selectors.FORM_NAME)),
      saveEnabled: formSelectors.saveEnabled(formSelectors.form(state, selectors.FORM_NAME))
    }),
    (dispatch) => ({
      save(formData, onSave) {
        dispatch(formActions.saveForm(selectors.FORM_NAME,
          formData.id
            ? ['apiv2_privacy_update', {id: formData.id}]
            : ['apiv2_privacy_create'])).then((response) => {
          onSave(response)
        })
      }
    })
  )(ThermsModalComponent)
)

export {TermsModal}
