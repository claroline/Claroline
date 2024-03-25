import {connect} from 'react-redux'

import {hasPermission} from '#/main/app/security'
import {withReducer} from '#/main/app/store/reducer'
import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {ShortcutResource as ShortcutResourceComponent} from '#/plugin/link/resources/shortcut/components/resource'
import {reducer, selectors} from '#/plugin/link/resources/shortcut/store'

const ShortcutResource = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      editable: hasPermission('edit', resourceSelectors.resourceNode(state))
    })
  )(ShortcutResourceComponent)
)

export {
  ShortcutResource
}