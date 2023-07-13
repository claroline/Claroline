import {connect} from 'react-redux'
import {withReducer} from '#/main/app/store/reducer'
import {PrivacyMain as PrivacyMainComponent} from '#/main/privacy/account/privacy/components/main'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {reducer, selectors, actions} from '#/main/privacy/account/privacy/store'

const PrivacyMain = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      loaded: selectors.loaded(state),
      privacyParameters: selectors.privacyParameters(state),
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
  )(PrivacyMainComponent)
)

export {
  PrivacyMain
}
