import {connect} from 'react-redux'

import {PrivacyTool as PrivacyToolComponent} from '../components/tool'
import {selectors} from '../store'
import {selectors as toolSelectors} from '#/main/core/tool/store'

const PrivacyTool = connect(
  (state) => ({
    path: toolSelectors.path(state),
    lockedParameters: selectors.lockedParameters(state),
    parameters: selectors.form(state)
  })
)(PrivacyToolComponent)

export {
  PrivacyTool
}
