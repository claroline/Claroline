import {createSelector} from 'reselect'
import cloneDeep from 'lodash/cloneDeep'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'
import set from 'lodash/set'

import {select as listSelectors} from '#/main/app/content/list/store'
import {Directory as DirectoryTypes} from '#/main/core/resources/directory/prop-types'

const explorer = (state, name) => get(state, name)

const loading = (explorerState) => explorerState.loading
const root = (explorerState) => explorerState.root
const filters = (explorerState) => explorerState.filters
const currentId = (explorerState) => explorerState.currentId
const currentNode = (explorerState) => explorerState.currentNode
const directories = (explorerState) => explorerState.directories
const selected = (explorerState) => listSelectors.selected(listSelectors.list(explorerState, 'resources'))
const selectedFull = (explorerState) => listSelectors.selectedFull(listSelectors.list(explorerState, 'resources'))
const currentResources = (explorerState) => listSelectors.data(listSelectors.list(explorerState, 'resources'))
const currentDirectory = (explorerState) => explorerState.currentConfiguration || DirectoryTypes.defaultProps

const showSummary = createSelector(
  [currentDirectory],
  (currentDirectory) => !!get(currentDirectory, 'display.showSummary')
)

const openSummary = createSelector(
  [currentDirectory],
  (currentDirectory) => !!get(currentDirectory, 'display.openSummary')
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

const directory = (dirs, directoryId) => {
  for (let i = 0; i < dirs.length; i++) {
    if (dirs[i].id === directoryId) {
      return dirs[i]
    } else if (dirs[i].children) {
      return directory(dirs[i].children, directoryId)
    }
  }

  return null
}

export const selectors = {
  explorer,
  loading,
  root,
  filters,
  currentId,
  currentNode,
  listConfiguration,
  currentResources,
  directories,
  directory,
  selected,
  selectedFull,
  showSummary,
  openSummary
}
