import {connect} from 'react-redux'

import {selectors} from '#/main/privacy/administration/privacy/store/selectors'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {PrivacyTool as PrivacyToolComponent} from '#/main/privacy/administration/privacy/components/tool'

const PrivacyTool = connect(
  (state) => ({
    path: toolSelectors.path(state),
    lockedParameters: selectors.lockedParameters(state),
    parameters: selectors.parameters(state)
  })
)(PrivacyToolComponent)

export {
  PrivacyTool
}
