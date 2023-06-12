import {connect} from 'react-redux'
import {withReducer} from '#/main/app/store/components/withReducer'
import {PrivacyTool as PrivacyToolComponent} from '#/main/privacy/account/privacy/components/tool'
import {selectors} from '#/main/privacy/account/privacy/store/selectors'
import {reducer} from '#/main/privacy/account/privacy/store/reducer'
import {selectors as securitySelectors} from '#/main/app/security/store/selectors'
import {actions} from '#/main/privacy/account/privacy/store/actions'

const PrivacyTool = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      dpo: selectors.dpo(state),
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
  )(PrivacyToolComponent)
)

export {
  PrivacyTool
}
