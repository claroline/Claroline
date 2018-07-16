import {API_REQUEST, url} from '#/main/app/api'
import {makeInstanceActionCreator} from '#/main/app/store/actions'
import {actions as listActions} from '#/main/core/data/list/actions'

// actions
export const EXPLORER_SET_ROOT = 'EXPLORER_SET_ROOT'
export const EXPLORER_SET_CURRENT = 'EXPLORER_SET_CURRENT'
export const EXPLORER_SET_INITIALIZED = 'EXPLORER_SET_INITIALIZED'
export const DIRECTORY_TOGGLE_OPEN = 'DIRECTORY_TOGGLE_OPEN'
export const DIRECTORIES_LOAD = 'DIRECTORIES_LOAD'

// actions creators
export const actions = {}

actions.setRoot = makeInstanceActionCreator(EXPLORER_SET_ROOT, 'root')
actions.setCurrent = makeInstanceActionCreator(EXPLORER_SET_CURRENT, 'current')
actions.setInitialized = makeInstanceActionCreator(EXPLORER_SET_INITIALIZED, 'initialized')

actions.initialize = (explorerName, root = null, current = null, filters = []) => (dispatch) => {
  dispatch(actions.setRoot(explorerName, root))
  dispatch(actions.setCurrent(explorerName, current || root))

  if (filters && filters.length > 0) {
    filters.forEach(f => {
      const property = Object.keys(f)[0]
      dispatch(listActions.addFilter(explorerName+'.resources', property, f[property]))
    })
  }

  dispatch(actions.setInitialized(explorerName, true))
}

actions.refresh = (explorerName, updatedNodes) => (dispatch) => {
  // refresh current directory list
  dispatch(listActions.invalidateData(explorerName+'.resources'))

  // update current if needed

  // update root if needed

  // refresh summary
}

actions.openDirectory = (explorerName, directory) => (dispatch) => {
  dispatch(actions.setCurrent(explorerName, directory))
  // mark directory has opened
  dispatch(actions.toggleDirectoryOpen(explorerName, directory, true))
}

actions.setDirectoryOpen = makeInstanceActionCreator(DIRECTORY_TOGGLE_OPEN, 'directory', 'opened')

actions.loadDirectories = makeInstanceActionCreator(DIRECTORIES_LOAD, 'parent', 'directories')
actions.fetchDirectories = (explorerName, parent = null) => ({
  [API_REQUEST]: {
    url: url(['apiv2_resource_list', {parent: parent ? parent.id : null}], {
      filters: {
        resourceType: 'directory'
      },
      sortBy: '-name'
    }),
    success: (response, dispatch) => dispatch(actions.loadDirectories(explorerName, parent, response.data || []))
  }
})

actions.toggleDirectoryOpen = (explorerName, directory, opened) => (dispatch) => {
  if (opened && !directory._loaded)  {
    dispatch(actions.fetchDirectories(explorerName, directory))
  }

  dispatch(actions.setDirectoryOpen(explorerName, directory, opened))
}
