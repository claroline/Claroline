import merge from 'lodash/merge'

import GRID_DISPLAYS from '#/main/app/content/list/grid'
import TABLE_DISPLAYS from '#/main/app/content/list/table'
import TREE_DISPLAYS from '#/main/app/content/list/tree'

/**
 * Registers implemented display modes for the data list component.
 */
export default merge({}, GRID_DISPLAYS, TABLE_DISPLAYS, TREE_DISPLAYS)
