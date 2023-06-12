import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {PrivacyTool as PrivacyToolComponent} from '#/main/privacy/account/privacy/components/tool'
import {actions, reducer, selectors} from '#/main/privacy/account/privacy/store'
import {selectors as securitySelectors} from '#/main/app/security/store/selectors'

const PrivacyTool = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      currentUser: securitySelectors.currentUser(state),
      accountPrivacy: selectors.accountPrivacy(state)
    }),
    (dispatch) => ({
      acceptTerms() {
        dispatch(actions.acceptTerms())
      },
      exportAccount() {
        dispatch(actions.exportAccount())
      },
      load() {
        dispatch(actions.fetchAccountPrivacy())
      }
    })
  )(PrivacyToolComponent)
)

export {
  PrivacyTool
}
