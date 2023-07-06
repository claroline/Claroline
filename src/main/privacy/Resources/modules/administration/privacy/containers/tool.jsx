import {connect} from 'react-redux'
import {PrivacyTool as PrivacyToolComponent} from '#/main/privacy/administration/privacy/components/tool'
import {selectors, actions} from '#/main/privacy/administration/privacy/store'

const PrivacyTool = connect(
  (state) => ({
    dpo: selectors.dpo(state),
    countryStorage: selectors.countryStorage(state),
    termsOfService: selectors.termsOfService(state),
    termsOfServiceEnabled: selectors.termsOfServiceEnabled(state)
  }),
  (dispatch) => ({
    updateCountry(countryStorage) {
      dispatch(actions.updateCountry(countryStorage))
    },
    updateDpo(dpo) {
      dispatch(actions.updateDpo(dpo))
    },
    updateTermsOfService(termsOfService) {
      dispatch(actions.updateTermsOfService(termsOfService))
    },
    updateTermsEnabled(termsOfServiceEnabled) {
      dispatch(actions.updateTermsEnabled(termsOfServiceEnabled))
    }
  })
)(PrivacyToolComponent)

export {
  PrivacyTool
}
