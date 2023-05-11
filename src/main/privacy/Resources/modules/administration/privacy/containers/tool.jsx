import {connect} from 'react-redux'

import {PrivacyTool as PrivacyToolComponent} from '../components/tool'
import {selectors} from '../store/selectors'

const PrivacyTool = connect(
  (state) => ({
    myData: selectors.store(state)
  }),
  (dispatch) => ({
    // inject Redux actions inside your component
  })
)(PrivacyToolComponent)

export {
  PrivacyTool
}
