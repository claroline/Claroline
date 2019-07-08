import {connect} from 'react-redux'

import {PlayerMenu as PlayerMenuComponent} from '#/plugin/path/resources/path/player/components/menu'
import {selectors} from '#/plugin/path/resources/path/store'

const PlayerMenu = connect(
  (state) => ({
    steps: selectors.steps(state)
  })
)(PlayerMenuComponent)

export {
  PlayerMenu
}
