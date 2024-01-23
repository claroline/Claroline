import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors} from '#/main/privacy/administration/privacy/store'

import {PrivacyTool as PrivacyToolComponent} from '#/main/privacy/administration/privacy/components/tool'

const PrivacyTool = connect(
  (state) => ({
    path: toolSelectors.path(state),
    parameters: selectors.parameters(state)
  })
)(PrivacyToolComponent)

export {
  PrivacyTool
}
