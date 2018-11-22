import {API_REQUEST, url} from '#/main/app/api'
import {makeInstanceActionCreator} from '#/main/app/store/actions'
import {actions as listActions} from '#/main/app/content/list/store'

import {selectors} from '#/main/core/resource/explorer/store/selectors'

// actions
export const EXPLORER_SET_LOADING = 'EXPLORER_SET_LOADING'
export const EXPLORER_SET_ROOT = 'EXPLORER_SET_ROOT'
export const EXPLORER_SET_CURRENT_ID = 'EXPLORER_SET_CURRENT_ID'
export const EXPLORER_SET_CURRENT_NODE = 'EXPLORER_SET_CURRENT_NODE'
export const EXPLORER_SET_CURRENT_CONFIGURATION = 'EXPLORER_SET_CURRENT_CONFIGURATION'
export const EXPLORER_SET_FILTERS = 'EXPLORER_SET_FILTERS'
export const DIRECTORY_TOGGLE_OPEN = 'DIRECTORY_TOGGLE_OPEN'
export const DIRECTORIES_LOAD = 'DIRECTORIES_LOAD'
export const DIRECTORY_UPDATE = 'DIRECTORY_UPDATE'

// actions creators
export const actions = {}

actions.setLoading = makeInstanceActionCreator(EXPLORER_SET_LOADING, 'loading')
actions.setRoot = makeInstanceActionCreator(EXPLORER_SET_ROOT, 'root')
actions.setCurrentId = makeInstanceActionCreator(EXPLORER_SET_CURRENT_ID, 'currentId')
actions.setCurrentNode = makeInstanceActionCreator(EXPLORER_SET_CURRENT_NODE, 'current')
actions.setCurrentConfiguration = makeInstanceActionCreator(EXPLORER_SET_CURRENT_CONFIGURATION, 'currentConfiguration', 'explorerFilters')
actions.setFilters = makeInstanceActionCreator(EXPLORER_SET_FILTERS, 'filters')
actions.updateDirectory = makeInstanceActionCreator(DIRECTORY_UPDATE, 'updatedDirectory')

/**
 * Initializes the explorer.
 *
 * NB. It can also be initialized by setting the initial state of the store.
 * This is mostly useful when the store initialization is not directly accessible (like in modals).
 *
 * @param explorerName
 * @param root
 * @param filters
 */
actions.initialize = (explorerName, root = null, filters = []) => (dispatch) => {
  dispatch(actions.setRoot(explorerName, root))
  dispatch(actions.setFilters(explorerName, filters))

  if (!root) {
    dispatch(actions.fetchDirectories(explorerName))
  }
}

/**
 * Changes the current directory of the explorer.
 *
 * @param {string}      explorerName
 * @param {string|null} directoryId
 */
actions.changeDirectory = (explorerName, directoryId = null) => (dispatch, getState) => {
  const oldId = selectors.currentId(selectors.explorer(getState(), explorerName))
  // grab filters fixed by the explorer
  const filters = selectors.filters(selectors.explorer(getState(), explorerName))

  // store current id now to make the ListData load the correct data
  dispatch(actions.setCurrentId(explorerName, directoryId))

  if (directoryId) {
    // mark directory has opened
    dispatch(actions.toggleDirectoryOpen(explorerName, directoryId, true))

    // check if the current directory as changed
    if (oldId !== directoryId) {
      // current directory as changed
      dispatch(actions.fetchCurrentDirectory(explorerName, directoryId, filters))
    }
  } else {
    dispatch(actions.setCurrentNode(explorerName, null))
    dispatch(actions.setCurrentConfiguration(explorerName, null, filters))

    // Load the list of resource for the current directory
    dispatch(listActions.fetchData(explorerName +'.resources', ['apiv2_resource_list'], true))

    // Load summary directories if not already done
    const directories = selectors.directories(selectors.explorer(getState(), explorerName))
    if (0 === directories.length) {
      dispatch(actions.fetchDirectories(explorerName))
    }
  }
}

actions.setDirectoryOpen = makeInstanceActionCreator(DIRECTORY_TOGGLE_OPEN, 'directoryId', 'opened')

actions.loadDirectories = makeInstanceActionCreator(DIRECTORIES_LOAD, 'parentId', 'directories')
actions.fetchDirectories = (explorerName, parentId = null) => ({
  [API_REQUEST]: {
    url: url(['apiv2_resource_list', {parent: parentId}], {
      filters: {
        resourceType: 'directory'
      },
      sortBy: 'name',
      // todo: lazy load instead
      limit: 20
    }),
    success: (response, dispatch) => dispatch(actions.loadDirectories(explorerName, parentId, response.data || []))
  }
})

actions.fetchCurrentDirectory = (explorerName, directoryId, filters = []) => ({
  [API_REQUEST]: {
    url: ['claro_resource_load_short', {id: directoryId}],
    before: (dispatch) => dispatch(actions.setLoading(explorerName, true)),
    success: (response, dispatch) => {
      // load directory node
      dispatch(actions.setCurrentNode(explorerName, response.resourceNode))
      // load directory resource (for list & display config)
      dispatch(actions.setCurrentConfiguration(explorerName, response.directory, filters))

      // Load the list of resource for the current directory
      dispatch(listActions.fetchData(explorerName +'.resources', ['apiv2_resource_list', {parent: directoryId}], true))

      dispatch(actions.setLoading(explorerName, false))
    },
    error: (errors, dispatch) => dispatch(actions.setLoading(explorerName, false))
  }
})

actions.toggleDirectoryOpen = (explorerName, directoryId, opened) => (dispatch, getState) => {
  if (opened) {
    // get the current directory to know if we need to load its children or not for summary
    const directory = selectors.directory(selectors.directories(selectors.explorer(getState(), explorerName)), directoryId)
    if (directory && !directory._loaded)  {
      dispatch(actions.fetchDirectories(explorerName, directoryId))
    }
  }

  dispatch(actions.setDirectoryOpen(explorerName, directoryId, opened))
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
  const current = selectors.currentNode(explorerState)
  const updatedCurrent = updatedNodes.find(node => current.id === node.id)
  if (updatedCurrent) {
    dispatch(actions.setCurrentNode(explorerName, updatedCurrent))
  }

  // check if root has been updated
  const root = selectors.root(explorerState)
  const updatedRoot = updatedNodes.find(node => root.id === node.id)
  if (updatedRoot) {
    dispatch(actions.setRoot(explorerName, updatedRoot))
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
  const current = selectors.currentNode(explorerState)
  if (-1 !== deletedNodes.findIndex(node => node.is === current.id)) {
    dispatch(actions.setCurrentNode(explorerName, selectors.directory(selectors.directories(explorerState), current.parent.id)))
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
  const current = selectors.currentNode(explorerState)

  if (current && -1 !== updatedNodes.findIndex(node => node.parent && current.id === node.parent.id)) {
    // we are inside a directory and one of the child have changed
    dispatch(listActions.fetchData(explorerName +'.resources', ['apiv2_resource_list', {parent: current.id}], true))
  } else if (-1 !== updatedNodes.findIndex(node => !!node.parent)) {
    dispatch(listActions.fetchData(explorerName +'.resources', ['apiv2_resource_list'], true))
  }
}
