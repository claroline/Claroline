import {API_REQUEST} from '#/main/app/api'
import {makeActionCreator} from '#/main/app/store/actions'
import {actions as listActions} from '#/main/app/content/list/store'

import {selectors} from '#/main/core/modals/resources/store/selectors'

// actions
export const EXPLORER_SET_LOADING = 'EXPLORER_SET_LOADING'
export const EXPLORER_SET_ROOT = 'EXPLORER_SET_ROOT'
export const EXPLORER_SET_CURRENT_ID = 'EXPLORER_SET_CURRENT_ID'
export const EXPLORER_SET_CURRENT_NODE = 'EXPLORER_SET_CURRENT_NODE'
export const EXPLORER_SET_CURRENT_CONFIGURATION = 'EXPLORER_SET_CURRENT_CONFIGURATION'
export const EXPLORER_SET_FILTERS = 'EXPLORER_SET_FILTERS'

// actions creators
export const actions = {}

actions.setLoading = makeActionCreator(EXPLORER_SET_LOADING, 'loading')
actions.setRoot = makeActionCreator(EXPLORER_SET_ROOT, 'root')
actions.setCurrentId = makeActionCreator(EXPLORER_SET_CURRENT_ID, 'currentId')
actions.setCurrentNode = makeActionCreator(EXPLORER_SET_CURRENT_NODE, 'current')
actions.setCurrentConfiguration = makeActionCreator(EXPLORER_SET_CURRENT_CONFIGURATION, 'currentConfiguration', 'explorerFilters')
actions.setFilters = makeActionCreator(EXPLORER_SET_FILTERS, 'filters')

/**
 * Initializes the explorer.
 *
 * NB. It can also be initialized by setting the initial state of the store.
 * This is mostly useful when the store initialization is not directly accessible (like in modals).
 *
 * @param root
 * @param filters
 */
actions.initialize = (root = null, filters = []) => (dispatch) => {
  dispatch(actions.setRoot(root))
  dispatch(actions.setFilters(filters))
}

/**
 * Changes the current directory of the explorer.
 *
 * @param {string|null} directoryId
 */
actions.changeDirectory = (directoryId = null) => (dispatch, getState) => {
  // grab filters fixed by the explorer
  const filters = selectors.filters(getState())

  // store current id now to make the ListData load the correct data
  dispatch(actions.setCurrentId(directoryId))

  if (directoryId) {
    dispatch(actions.fetchCurrentDirectory(directoryId, filters))
  } else {
    dispatch(actions.setCurrentNode(null))
    dispatch(actions.setCurrentConfiguration(null, filters))

    // Load the list of resource for the current directory
    dispatch(listActions.fetchData(selectors.STORE_NAME +'.resources', ['apiv2_resource_list'], true))
  }
}

actions.fetchCurrentDirectory = (directoryId, filters = []) => ({
  [API_REQUEST]: {
    url: ['claro_resource_load_short', {id: directoryId}],
    before: (dispatch) => dispatch(actions.setLoading(true)),
    success: (response, dispatch) => {
      // load directory node
      dispatch(actions.setCurrentNode(response.resourceNode))
      // load directory resource (for list & display config)
      dispatch(actions.setCurrentConfiguration(response.directory, filters))

      // Load the list of resource for the current directory
      dispatch(listActions.fetchData(selectors.STORE_NAME +'.resources', ['apiv2_resource_list', {parent: directoryId}], true))

      dispatch(actions.setLoading(false))
    },
    error: (errors, status, dispatch) => dispatch(actions.setLoading(false))
  }
})
