import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {ApiAdministration as ApiAdministrationComponent}  from '#/main/core/integration/api/components/administration'

const ApiAdministration = connect(
  (state) => ({
    path: toolSelectors.path(state)
  })
)(ApiAdministrationComponent)

export {
  ApiAdministration
}
