import {connect} from 'react-redux'

import {ShortcutResource as ShortcutResourceComponent} from '#/plugin/link/resources/shortcut/components/resource'
import {selectors} from '#/plugin/link/resources/shortcut/store'

const ShortcutResource = connect(
  (state) => ({
    resource: selectors.embeddedResource(state)
  })
)(ShortcutResourceComponent)

export {
  ShortcutResource
}