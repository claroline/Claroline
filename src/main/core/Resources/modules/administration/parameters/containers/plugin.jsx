import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store/selectors'

import {Plugin as PluginComponent} from '#/main/core/administration/parameters/components/plugin'
import {actions, selectors} from '#/main/core/administration/parameters/store'

const Plugin = connect(
  (state) => ({
    path: toolSelectors.path(state),
    plugin: selectors.plugin(state)
  }),
  (dispatch) => ({
    enable(plugin) {
      dispatch(actions.enable(plugin))
    },
    disable(plugin) {
      dispatch(actions.disable(plugin))
    }
  })
)(PluginComponent)

export {
  Plugin
}
