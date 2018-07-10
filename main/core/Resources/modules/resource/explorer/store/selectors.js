import get from 'lodash/get'

import {select as listSelectors} from '#/main/core/data/list/selectors'

const explorer = (state, name) => get(state, name)

const initialized = (explorerState) => explorerState.initialized
const root = (explorerState) => explorerState.root
const current = (explorerState) => explorerState.current
const directories = (explorerState) => explorerState.directories
const selected = (explorerState) => listSelectors.selected(listSelectors.list(explorerState, 'resources'))
const selectedFull = (explorerState) => listSelectors.selectedFull(listSelectors.list(explorerState, 'resources'))

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
  initialized,
  explorer,
  root,
  current,
  directories,
  directory,
  selected,
  selectedFull
}
