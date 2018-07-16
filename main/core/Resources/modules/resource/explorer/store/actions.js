import {API_REQUEST, url} from '#/main/app/api'
import {makeInstanceActionCreator} from '#/main/app/store/actions'
import {actions as listActions} from '#/main/core/data/list/actions'

import {selectors} from '#/main/core/resource/explorer/store/selectors'

// actions
export const EXPLORER_SET_ROOT = 'EXPLORER_SET_ROOT'
export const EXPLORER_SET_CURRENT = 'EXPLORER_SET_CURRENT'
export const EXPLORER_SET_INITIALIZED = 'EXPLORER_SET_INITIALIZED'
export const DIRECTORY_TOGGLE_OPEN = 'DIRECTORY_TOGGLE_OPEN'
export const DIRECTORIES_LOAD = 'DIRECTORIES_LOAD'
export const DIRECTORY_UPDATE = 'DIRECTORY_UPDATE'

// actions creators
export const actions = {}

actions.setRoot = makeInstanceActionCreator(EXPLORER_SET_ROOT, 'root')
actions.setCurrent = makeInstanceActionCreator(EXPLORER_SET_CURRENT, 'current')
actions.setInitialized = makeInstanceActionCreator(EXPLORER_SET_INITIALIZED, 'initialized')
actions.updateDirectory = makeInstanceActionCreator(DIRECTORY_UPDATE, 'updatedDirectory')

actions.initialize = (explorerName, root = null, current = null, filters = []) => (dispatch) => {
  dispatch(actions.setRoot(explorerName, root))
  dispatch(actions.setCurrent(explorerName, current || root))

  if (root) {
    dispatch(actions.loadDirectories(explorerName, [root]))
  }

  if (filters && filters.length > 0) {
    filters.forEach(f => {
      const property = Object.keys(f)[0]
      dispatch(listActions.addFilter(explorerName+'.resources', property, f[property]))
    })
  }

  dispatch(actions.setInitialized(explorerName, true))
}

actions.openDirectory = (explorerName, directory) => (dispatch) => {
  dispatch(actions.setCurrent(explorerName, directory))
  // mark directory has opened
  dispatch(actions.toggleDirectoryOpen(explorerName, directory, true))
}

actions.setDirectoryOpen = makeInstanceActionCreator(DIRECTORY_TOGGLE_OPEN, 'directoryId', 'opened')

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

  dispatch(actions.setDirectoryOpen(explorerName, directory.id, opened))
}

actions.addNodes = (explorerName, createdNodes) => (dispatch, getState) => {
  const explorerState = selectors.explorer(getState(), explorerName)

  // reset list if new nodes are added to the current directory
  dispatch(actions.invalidateCurrentResources(explorerName, createdNodes))

  // add new directories in summaries if they are visible (aka. parent directory displayed and its children loaded)
  const directories = selectors.directories(explorerState)
  const newDirectories = createdNodes
    // only get directories
    .filter(node => 'directory' === node.meta.type)
    // group created directories by parent
    .reduce((acc, current) => {
      if (!acc[current.parent.id]) {
        acc[current.parent.id] = []
      }

      acc[current.parent.id].push(current)

      return acc
    }, {})
  Object.keys(newDirectories).map(parentId => {
    const parent = selectors.directory(directories, parentId)
    if (parent && parent._opened) {
      dispatch(actions.loadDirectories(explorerName, parent, []
        .concat(parent.children, newDirectories[parentId])
        .sort((a, b) => a.name < b.name)
      ))
    }
  })
}

actions.updateNodes = (explorerName, updatedNodes) => (dispatch, getState) => {
  const explorerState = selectors.explorer(getState(), explorerName)

  // check if current has been updated
  const current = selectors.current(explorerState)
  const updatedCurrent = updatedNodes.find(node => current.id === node.id)
  if (updatedCurrent) {
    dispatch(actions.setCurrent(updatedCurrent))
  }

  // check if root has been updated
  const root = selectors.root(explorerState)
  const updatedRoot = updatedNodes.find(node => root.id === node.id)
  if (updatedRoot) {
    dispatch(actions.setRoot(updatedRoot))
  }

  // reset list if new nodes are updated in the current directory
  dispatch(actions.invalidateCurrentResources(explorerName, updatedNodes))

  // update directories in summaries if they are visible (aka. parent directory displayed and its children loaded)
  updatedNodes
    // only get directories
    .filter(node => 'directory' === node.meta.type)
    .map(directory => dispatch(actions.updateDirectory(explorerName, directory)))
}

actions.deleteNodes = (explorerName, deletedNodes) => (dispatch, getState) => {
  const explorerState = selectors.explorer(getState(), explorerName)

  // move explorer root if it is deleted
  // (for now it cannot occur because root is only used by WS roots which are not deletable)
  // we may need to implement it later

  // change current directory if it is deleted
  const current = selectors.current(explorerState)
  if (-1 !== deletedNodes.findIndex(node => node.is === current.id)) {
    dispatch(actions.setCurrent(selectors.directory(selectors.directories(explorerState), current.parent.id)))
  } else {
    // reset list if nodes are deleted from the current directory
    dispatch(actions.invalidateCurrentResources(explorerName, deletedNodes))
  }

  // remove directories in summaries if they are visible (aka. parent directory displayed and its children loaded)
  const directories = selectors.directories(explorerState)
  const deletedDirectories = deletedNodes
    // only get directories
    .filter(node => 'directory' === node.meta.type)
    // group deleted directories by parent
    .reduce((acc, current) => {
      if (!acc[current.parent.id]) {
        acc[current.parent.id] = []
      }

      acc[current.parent.id].push(current)

      return acc
    }, {})

  Object.keys(deletedDirectories).map(parentId => {
    const parent = selectors.directory(directories, parentId)
    if (parent && parent._opened) {
      dispatch(actions.loadDirectories(explorerName, parent, []
        .concat(parent.children)
        .splice(parent.children.findIndex(child => child.id === deletedDirectories[parentId]), 1)
      ))
    }
  })
}

actions.invalidateCurrentResources = (explorerName, updatedNodes) => (dispatch, getState) => {
  const explorerState = selectors.explorer(getState(), explorerName)
  const current = selectors.current(explorerState)

  if (-1 !== updatedNodes.findIndex(node => current.id === node.parent.id)) {
    dispatch(listActions.invalidateData(explorerName+'.resources'))
  }
}