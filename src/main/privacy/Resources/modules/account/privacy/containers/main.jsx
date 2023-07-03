import {connect} from 'react-redux'

import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors} from '#/main/privacy/account/privacy/store'
import {actions} from '#/main/privacy/account/privacy/store'

import {PrivacyMain as PrivacyMainComponent} from '#/main/privacy/account/privacy/components/main'

const PrivacyMain = connect(
  (state) => ({
    /*privacy: selectors.privacy(state),*/
    currentUser: securitySelectors.currentUser(state)
  }),
  (dispatch) => ({
    acceptTerms() {
      dispatch(actions.acceptTerms())
    },
    exportAccount() {
      dispatch(actions.exportAccount())
    }
    /*fetchAccountPrivacy() {
      dispatch(actions.fetchAccountPrivacy())
    },
    updateAccountPrivacy(privacy) {
      dispatch(actions.updateAccountPrivacy(privacy))
    }*/
  })
)(PrivacyMainComponent)

export {
  PrivacyMain
}