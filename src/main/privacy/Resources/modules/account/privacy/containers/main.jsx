import { connect } from 'react-redux'
import { withReducer } from '#/main/app/store/components/withReducer'
import { PrivacyMain as PrivacyMainComponent } from '#/main/privacy/account/privacy/components/main'
import { actions, reducer, selectors } from '#/main/privacy/account/privacy/store'
import { selectors as securitySelectors } from '#/main/app/security/store/selectors'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'

const PrivacyContainers = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      formData: formSelectors.data(formSelectors.form(state, selectors.STORE_NAME)),
      currentUser: securitySelectors.currentUser(state),
      parameters: state.parameters
    }),
    (dispatch) => ({
      reset(dpo) {
        dispatch(formActions.resetForm(selectors.STORE_NAME, {'dpo':dpo}, false))
      },
      acceptTerms() {
        dispatch(actions.acceptTerms())
      },
      exportAccount() {
        dispatch(actions.exportAccount())
      }
    })
  )(PrivacyMainComponent)
)

export { PrivacyContainers }
