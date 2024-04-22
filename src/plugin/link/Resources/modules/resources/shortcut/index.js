import {declareResource} from '#/main/core/resource'
import {ShortcutCreation} from '#/plugin/link/resources/shortcut/components/creation'
import {ShortcutResource} from '#/plugin/link/resources/shortcut/containers/resource'

/**
 * Shortcut creation app.
 */
export const Creation = () => ({
  component: ShortcutCreation
})

export default declareResource(ShortcutResource)
