import {connect} from 'react-redux'

import {PluginsTool as PluginsToolComponent} from '#/main/core/administration/plugins/components/tool'
import {actions} from '#/main/core/administration/plugins/store'

const PluginsTool = connect(
  null,
  (dispatch) => ({
    open(pluginId) {
      dispatch(actions.open(pluginId))
    }
  })
)(PluginsToolComponent)

export {
  PluginsTool
}
