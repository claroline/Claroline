import {connect} from 'react-redux'
import {withReducer} from '#/main/app/store/reducer'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {ParametersTool as ParametersToolComponent} from '#/main/core/administration/parameters/components/tool'
import {selectors, reducer} from '#/main/core/administration/parameters/store'

const ParametersTool = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      path: toolSelectors.path(state)
    })
  )(ParametersToolComponent)
)

export {
  ParametersTool
}
