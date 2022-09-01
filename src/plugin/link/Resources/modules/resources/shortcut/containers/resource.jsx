import {connect} from 'react-redux'

import {hasPermission} from '#/main/app/security'
import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {ShortcutResource as ShortcutResourceComponent} from '#/plugin/link/resources/shortcut/components/resource'

const ShortcutResource = connect(
  (state) => ({
    editable: hasPermission('edit', resourceSelectors.resourceNode(state))
  })
)(ShortcutResourceComponent)

export {
  ShortcutResource
}