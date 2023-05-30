import {connect} from 'react-redux'
import {actions} from '#/main/privacy/account/privacy/store'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {PrivacyMain as PrivacyMainComponent} from '#/main/privacy/account/privacy/components/tool'
import {selectors} from '#/main/privacy/account/privacy/store'

const PrivacyMain = connect(
  (state) => ({
    parameters: selectors.store(state),
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
