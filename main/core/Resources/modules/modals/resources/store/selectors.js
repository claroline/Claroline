import {createSelector} from 'reselect'
import cloneDeep from 'lodash/cloneDeep'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'
import set from 'lodash/set'

import {select as listSelectors} from '#/main/app/content/list/store'
import {Directory as DirectoryTypes} from '#/main/core/resources/directory/prop-types'

const STORE_NAME = 'resourceExplorer'

const explorer = (state) => get(state, STORE_NAME)

const loading = createSelector(
  [explorer],
  (explorerState) => explorerState.loading
)
const root = createSelector(
  [explorer],
  (explorerState) => explorerState.root
)
const filters = createSelector(
  [explorer],
  (explorerState) => explorerState.filters
)
const currentId = createSelector(
  [explorer],
  (explorerState) => explorerState.currentId
)
const currentNode = createSelector(
  [explorer],
  (explorerState) => explorerState.currentNode
)
const selected = (state) => listSelectors.selected(listSelectors.list(state, STORE_NAME+'.resources'))
const selectedFull = (state) => listSelectors.selectedFull(listSelectors.list(state, STORE_NAME+'.resources'))
const currentResources = (state) => listSelectors.data(listSelectors.list(state, STORE_NAME+'.resources'))
const currentDirectory = createSelector(
  [explorer],
  (explorerState) => explorerState.currentConfiguration || DirectoryTypes.defaultProps
)

const listConfiguration = createSelector(
  [currentDirectory, filters],
  (currentDirectory, filters) => {
    // when no directory just add the default props
    let configuration = cloneDeep(currentDirectory.list)

    if (!isEmpty(filters)) {
      const availableFilters = get(configuration, 'availableFilters') || []
      // check if the current directory is able to manage general explorer filters
      filters.map(filter => {
        // check if filter is in the available filters of the current dir
        // and add it if missing because the DataList will not be able to display it otherwise.
        if (-1 === availableFilters.indexOf(filter.property)) {
          availableFilters.push(filter.property)
        }
      })

      // update available filter list
      set(configuration, 'availableFilters', availableFilters)
    }

    return configuration
  }
)

export const selectors = {
  STORE_NAME,

  explorer,
  loading,
  root,
  filters,
  currentId,
  currentNode,
  listConfiguration,
  currentResources,
  selected,
  selectedFull
}
