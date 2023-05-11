import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors} from '../store'
import {PrivacyMain as PrivacyMainComponent} from '../components/main.jsx'

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