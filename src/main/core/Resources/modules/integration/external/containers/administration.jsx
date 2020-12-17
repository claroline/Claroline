import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {ExternalAdministration as ExternalAdministrationComponent} from '#/main/core/integration/external/components/administration'

const ExternalAdministration = connect(
  (state) => ({
    path: toolSelectors.path(state)
  })
)(ExternalAdministrationComponent)

export {
  ExternalAdministration
}
