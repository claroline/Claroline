import {connect} from 'react-redux'
import {withReducer} from '#/main/app/store/components/withReducer'
import {TermsModal as ThermsModalComponent} from '#/main/privacy/administration/privacy/modals/terms/components/modal'
import {selectors, reducer} from '#/main/privacy/administration/privacy/modals/terms/store'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'

const TermsModal = withReducer(selectors.STORE_NAME, reducer)(
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
      reset(termsOfService, termsOfServiceEnabled) {
        dispatch(formActions.resetForm(selectors.STORE_NAME, {'termsOfService':termsOfService, termsOfServiceEnabled:termsOfServiceEnabled}, false))
      }
    })
  )(ThermsModalComponent)
)

export {TermsModal}