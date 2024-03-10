import {connect} from 'react-redux'
import {withReducer} from '#/main/app/store/reducer'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {ParametersTool as ParametersToolComponent} from '#/main/core/administration/parameters/components/tool'
import {selectors, reducer, actions} from '#/main/core/administration/parameters/store'

const ParametersTool = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      path: toolSelectors.path(state)
    }),
    (dispatch) => ({
      openPlugin(pluginId) {
        dispatch(actions.openPlugin(pluginId))
      }
    })
  )(ParametersToolComponent)
)

export {
  ParametersTool
}
