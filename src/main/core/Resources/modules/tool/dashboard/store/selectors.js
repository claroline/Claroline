import {createSelector} from 'reselect'

import {selectors as toolSelectors} from '#/main/core/tool/store'

/**
 * Get the path of the current resource dashboard.
 * Used to create additional routing in the dashboard.
 */
const path = createSelector(
  [toolSelectors.path],
  (toolPath) => toolPath + '/dashboard'
)

export const selectors = {
  path
}
