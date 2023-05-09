import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors} from '#/main/privacy/administration/privacy/store'
import {PrivacyMain as PrivacyMainComponent} from '#/main/privacy/administration/privacy/components/main'

const PrivacyMain = connect(
  (state) => ({
    path: toolSelectors.path(state),
    lockedParameters: selectors.lockedParameters(state),
    parameters: selectors.parameters(state)
  })
)(PrivacyMainComponent)

export {
  PrivacyMain
}
