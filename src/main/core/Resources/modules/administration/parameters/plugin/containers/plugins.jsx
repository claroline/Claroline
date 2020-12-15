import {connect} from 'react-redux'

import {actions, selectors} from '#/main/core/administration/parameters/store'
import {Plugins as PluginsComponent} from '#/main/core/administration/parameters/plugin/components/plugins'

const Plugins = connect(
  (state) => ({
    plugins: selectors.plugins(state)
  }),
  (dispatch) => ({
    fetchPlugins() {
      dispatch(actions.fetchPlugins())
    }
  })
)(PluginsComponent)

export {
  Plugins
}
