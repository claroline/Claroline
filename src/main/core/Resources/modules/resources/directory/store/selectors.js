import {createSelector} from 'reselect'

const STORE_NAME = 'directory'
const LIST_NAME = STORE_NAME+'.resources'

const store = (state) => state[STORE_NAME]

const resource = createSelector(
  [store],
  (store) => store.resource
)

const directories = createSelector(
  [store],
  (store) => store.directories
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

const storageLock = createSelector(
  [store],
  (store) => store.storageLock
)

const listConfiguration = createSelector(
  [resource],
  (resource) => resource.list || {}
)

export const selectors = {
  STORE_NAME,
  LIST_NAME,

  resource,
  directories,
  directory,
  storageLock,
  listConfiguration
}
