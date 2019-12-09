import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors} from '#/main/core/administration/parameters/store/selectors'
import {Technical as TechnicalComponent} from '#/main/core/administration/parameters/technical/components/technical'

const Technical = connect(
  (state) => ({
    path: toolSelectors.path(state),
    lockedParameters: selectors.lockedParameters(state),
    mailer: selectors.parameters(state).mailer
  })
)(TechnicalComponent)

export {
  Technical
}
