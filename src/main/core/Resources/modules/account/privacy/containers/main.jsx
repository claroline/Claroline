import {connect} from 'react-redux'

import {selectors as configSelectors} from '#/main/app/config/store'
import {selectors as securitySelectors} from '#/main/app/security/store'

import {actions} from '#/main/core/account/privacy/store'
import {PrivacyMain as PrivacyMainComponent} from '#/main/core/account/privacy/components/main'

const PrivacyMain = connect(
  (state) => ({
    privacy: configSelectors.param(state, 'privacy'),
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
