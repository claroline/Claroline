import {connect} from 'react-redux'

import {ShortcutPlayer as ShortcutPlayerComponent} from '#/plugin/link/resources/shortcut/components/player'
import {selectors} from '#/plugin/link/resources/shortcut/store'

const ShortcutPlayer = connect(
  (state) => ({
    resource: selectors.embeddedResource(state)
  })
)(ShortcutPlayerComponent)

export {
  ShortcutPlayer
}