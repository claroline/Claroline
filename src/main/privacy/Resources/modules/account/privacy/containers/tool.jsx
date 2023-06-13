import { connect } from 'react-redux';
import { withReducer } from '#/main/app/store/components/withReducer'
import { PrivacyTool as PrivacyToolComponent } from '#/main/privacy/account/privacy/components/tool'
import { actions, reducer, selectors } from '#/main/privacy/account/privacy/store'
import { selectors as securitySelectors } from '#/main/app/security/store/selectors'

const mapStateToProps = (state) => ({
  privacyData: selectors.selectPrivacyData(state),
  currentUser: securitySelectors.currentUser(state)
})

const mapDispatchToProps = {
  acceptTerms: actions.acceptTerms,
  exportAccount: actions.exportAccount,
  fetchPrivacy: actions.fetchPrivacy
}

const PrivacyTool = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    mapStateToProps,
    mapDispatchToProps
  )(PrivacyToolComponent)
)

export { PrivacyTool }
