import {connect} from 'react-redux'

import {ToolPage as ToolPageComponent} from '#/main/core/tool/components/page'
import {actions, selectors} from '#/main/core/tool/store'

const ToolPage = connect(
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
  })
)(ToolPageComponent)

export {
  ToolPage
}
