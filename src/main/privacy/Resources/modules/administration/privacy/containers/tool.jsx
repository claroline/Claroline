import {connect} from 'react-redux'

import {selectors} from '#/main/privacy/administration/privacy/store/selectors'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {PrivacyTool as PrivacyToolComponent} from '#/main/privacy/administration/privacy/components/tool'

const PrivacyTool = connect(
  (state) => ({
    path: toolSelectors.path(state),
    parameters: selectors.parameters(state),
    data: selectors.store(state)
  })
)(PrivacyToolComponent)

export {
  PrivacyTool
}
