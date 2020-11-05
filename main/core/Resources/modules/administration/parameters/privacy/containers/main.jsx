import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors} from '#/main/core/administration/parameters/store/selectors'
import {PrivacyMain as PrivacyMainComponent} from '#/main/core/administration/parameters/privacy/components/main'

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
