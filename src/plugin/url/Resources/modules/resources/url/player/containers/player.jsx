import {connect} from 'react-redux'

import {Player as PlayerComponent} from '#/plugin/url/resources/url/player/components/player'
import {selectors} from '#/plugin/url/resources/url/store'

const Player = connect(
  (state) => ({
    url: selectors.url(state)
  })
)(PlayerComponent)

export {
  Player
}
