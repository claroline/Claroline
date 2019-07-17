import {connect} from 'react-redux'

import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {PlayerMenu as PlayerMenuComponent} from '#/plugin/path/resources/path/player/components/menu'
import {selectors} from '#/plugin/path/resources/path/store'

const PlayerMenu = connect(
  (state) => ({
    path: resourceSelectors.path(state),
    steps: selectors.steps(state)
  })
)(PlayerMenuComponent)

export {
  PlayerMenu
}
