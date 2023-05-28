import {connect} from 'react-redux'
import {PrivacyTool as PrivacyToolComponent} from '#/main/privacy/administration/privacy/components/tool'
import {selectors} from '#/main/privacy/administration/privacy/store/selectors'
const PrivacyTool = connect(
  (state) => ({
    parameters: selectors.store(state)
  }),
  (dispatch) => ({
    // inject Redux actions inside your component
  })
)(PrivacyToolComponent)

export {
  PrivacyTool
}
