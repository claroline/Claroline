import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {reducer, selectors} from '#/main/privacy/administration/privacy/store'

import {PrivacyTool as PrivacyToolComponent} from '#/main/privacy/administration/privacy/components/tool'
import {withReducer} from '#/main/app/store/reducer'

const PrivacyTool = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      path: toolSelectors.path(state),
      parameters: selectors.parameters(state)
    })
  )(PrivacyToolComponent)
)

export {
  PrivacyTool
}
