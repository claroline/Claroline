import {connect} from 'react-redux'

import {selectors as formSelect} from '#/main/app/content/form/store/selectors'
import {actions as formActions} from '#/main/app/content/form/store/actions'

import {ToolsTool as ToolsToolComponent} from '#/main/core/tools/parameters/tools/components/tool'
import {selectors} from '#/main/core/tools/parameters/store'

const ToolsTool = connect(
  (state) => ({
    tools: selectors.tools(state),
    toolsConfig: formSelect.data(formSelect.form(state, selectors.STORE_NAME+'.toolsConfig'))
  }),
  (dispatch) => ({
    updateProp(propName, propValue) {
      dispatch(formActions.updateProp(selectors.STORE_NAME+'.toolsConfig', propName, propValue))
    }
  })
)(ToolsToolComponent)

export {
  ToolsTool
}
