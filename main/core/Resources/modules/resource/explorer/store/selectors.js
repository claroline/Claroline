import get from 'lodash/get'

import {select as listSelectors} from '#/main/app/content/list/store'

const explorer = (state, name) => get(state, name)

const root = (explorerState) => explorerState.root
const filters = (explorerState) => explorerState.filters
const currentId = (explorerState) => explorerState.currentId
const currentNode = (explorerState) => explorerState.currentNode
const currentConfiguration = (explorerState) => explorerState.currentConfiguration
const directories = (explorerState) => explorerState.directories
const selected = (explorerState) => listSelectors.selected(listSelectors.list(explorerState, 'resources'))
const selectedFull = (explorerState) => listSelectors.selectedFull(listSelectors.list(explorerState, 'resources'))
const currentResources = (explorerState) => listSelectors.data(listSelectors.list(explorerState, 'resources'))

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
  root,
  filters,
  currentId,
  currentNode,
  currentConfiguration,
  currentResources,
  directories,
  directory,
  selected,
  selectedFull
}
