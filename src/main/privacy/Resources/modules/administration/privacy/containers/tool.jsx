import {connect} from 'react-redux'

import {selectors} from '#/main/privacy/administration/privacy/store/selectors'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {PrivacyTool as PrivacyToolComponent} from '#/main/privacy/administration/privacy/components/tool'
import {selectors as configSelectors} from '#/main/app/config/store'

const PrivacyTool = connect(
  (state) => ({
    path: toolSelectors.path(state),
    parameters: configSelectors.param(state, 'privacy')
  })
)(PrivacyToolComponent)

export {
  PrivacyTool
}
