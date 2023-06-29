import {connect} from 'react-redux'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {actions} from '#/main/privacy/account/privacy/store'
import {PrivacyMain as PrivacyMainComponent} from '#/main/privacy/account/privacy/components/main'

const PrivacyMain =
  connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state)
  }),
  (dispatch) => ({
    acceptTerms() {
      dispatch(actions.acceptTerms())
    },
    exportAccount() {
      dispatch(actions.exportAccount())
    }
  })
)(PrivacyMainComponent)

export {
  PrivacyMain
}