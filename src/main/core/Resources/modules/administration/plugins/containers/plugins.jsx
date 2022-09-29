import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store/selectors'

import {Plugins as PluginsComponent} from '#/main/core/administration/plugins/components/plugins'
import {actions} from '#/main/core/administration/plugins/store'

const Plugins = connect(
  (state) => ({
    path: toolSelectors.path(state)
  }),
  (dispatch) => ({
    enable(plugin) {
      dispatch(actions.enable(plugin))
    },
    disable(plugin) {
      dispatch(actions.disable(plugin))
    }
  })
)(PluginsComponent)

export {
  Plugins
}
