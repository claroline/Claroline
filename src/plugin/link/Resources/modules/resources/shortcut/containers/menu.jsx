import {connect} from 'react-redux'

import {ShortcutMenu as ShortcutMenuComponent} from '#/plugin/link/resources/shortcut/components/menu'
import {selectors} from '#/plugin/link/resources/shortcut/store'

const ShortcutMenu = connect(
  (state) => ({
    target: selectors.embeddedResource(state)
  })
)(ShortcutMenuComponent)

export {
  ShortcutMenu
}
