import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {ToolPage as ToolPageComponent} from '#/main/core/tool/components/page'
import {actions, reducer, selectors} from '#/main/core/tool/store'

const ToolPage = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      name: selectors.name(state),
      basePath: selectors.path(state),
      toolData: selectors.toolData(state),
      currentContext: selectors.context(state)
    }),
    (dispatch) => ({
      reload() {
        dispatch(actions.setLoaded(false))
      }
    }),
    undefined,
    {
      areStatesEqual: (next, prev) => selectors.store(prev) === selectors.store(next)
    }
  )(ToolPageComponent)
)

export {
  ToolPage
}
