import {connect} from 'react-redux'

import {selectors} from '#/main/core/tool/store/selectors'

import {ToolShortcutTabParameters as ToolShortcutTabParametersComponent} from '#/plugin/link/home/tool-shortcut/components/parameters'

const ToolShortcutTabParameters = connect(
  (state) => {
    console.log(selectors.contextTools(state))
    return ({
      tools: selectors.contextTools(state)
    })
  }
)(ToolShortcutTabParametersComponent)

export {
  ToolShortcutTabParameters
}
