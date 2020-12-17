import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {PlayerMenu as PlayerMenuComponent} from '#/plugin/home/tools/home/player/components/menu'
import {selectors} from '#/plugin/home/tools/home/player/store'

const PlayerMenu = withRouter(
  connect(
    (state) => ({
      path: toolSelectors.path(state),
      tabs: selectors.tabs(state)
    })
  )(PlayerMenuComponent)
)

export {
  PlayerMenu
}
