import {ShortcutCreation} from '#/plugin/link/resources/shortcut/components/creation'

import {ShortcutResource} from '#/plugin/link/resources/shortcut/containers/resource'
import {reducer} from '#/plugin/link/resources/shortcut/store'

/**
 * Shortcut creation app.
 */
export const Creation = () => ({
  component: ShortcutCreation
})

export default {
  component: ShortcutResource,
  store: reducer
}
