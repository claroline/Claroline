import {createSelector} from 'reselect'

const STORE_NAME = 'directory'

const resource = (state) => state[STORE_NAME]

const directories = createSelector(
  [resource],
  (resource) => resource.directories
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
  STORE_NAME,

  resource,
  current,
  directories,
  directory
}
