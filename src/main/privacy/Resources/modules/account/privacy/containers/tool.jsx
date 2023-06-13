import { connect } from 'react-redux';
import { withReducer } from '#/main/app/store/components/withReducer'
import { PrivacyTool as PrivacyToolComponent } from '#/main/privacy/account/privacy/components/tool'
import { actions, reducer, selectors } from '#/main/privacy/account/privacy/store'
import { selectors as securitySelectors } from '#/main/app/security/store/selectors'

const PrivacyTool = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
        loaded: selectors.loaded(state),
        privacyData: selectors.privacyData(state),
        currentUser: securitySelectors.currentUser(state)
      }),
    (dispatch) => ({
      fetch() {
        dispatch(actions.fetch())
      },
      acceptTerms() {
        dispatch(actions.acceptTerms())
      },
      exportAccount() {
        dispatch(actions.exportAccount())
      }
    })
  )(PrivacyToolComponent)
)

export { PrivacyTool }
