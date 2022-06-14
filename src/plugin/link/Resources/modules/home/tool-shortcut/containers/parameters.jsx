import {connect} from 'react-redux'

import {selectors} from '#/main/core/tool/store/selectors'

import {ToolShortcutTabParameters as ToolShortcutTabParametersComponent} from '#/plugin/link/home/tool-shortcut/components/parameters'

const ToolShortcutTabParameters = connect(
  (state) => ({
    tools: selectors.contextTools(state)
  })
)(ToolShortcutTabParametersComponent)

export {
  ToolShortcutTabParameters
}
