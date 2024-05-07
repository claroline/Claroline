import {withReducer} from '#/main/app/store/reducer'

import {ShortcutResource as ShortcutResourceComponent} from '#/plugin/link/resources/shortcut/components/resource'
import {reducer, selectors} from '#/plugin/link/resources/shortcut/store'

const ShortcutResource = withReducer(selectors.STORE_NAME, reducer)(
  ShortcutResourceComponent
)

export {
  ShortcutResource
}
