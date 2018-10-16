import cloneDeep from 'lodash/cloneDeep'
import difference from 'lodash/difference'
import get from 'lodash/get'
import merge from 'lodash/merge'
import unionBy from 'lodash/unionBy'

import {makeReducer, makeInstanceReducer, combineReducers, reduceReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'
import {constants as listConst} from '#/main/app/content/list/constants'

import {
  EXPLORER_SET_ROOT,
  EXPLORER_SET_CURRENT_ID,
  EXPLORER_SET_CURRENT_NODE,
  EXPLORER_SET_CURRENT_CONFIGURATION,
  EXPLORER_SET_FILTERS,
  DIRECTORY_TOGGLE_OPEN,
  DIRECTORIES_LOAD,
  DIRECTORY_UPDATE
} from '#/main/core/resource/explorer/store/actions'
import {selectors} from '#/main/core/resource/explorer/store/selectors'

/**
 * Replaces a directory data inside the directories tree.
 *
 * @param {Array}  directories - the directory tree
 * @param {object} newDir      - the new directory data
 *
 * @return {Array} - the updated directories tree
 */
function replaceDirectory(directories, newDir) {
  for (let i = 0; i < directories.length; i++) {
    if (directories[i].id === newDir.id) {
      const updatedDirs = cloneDeep(directories)
      updatedDirs[i] = newDir

      return updatedDirs
    } else if (directories[i].children) {
      const updatedDirs = cloneDeep(directories)
      updatedDirs[i].children = replaceDirectory(directories[i].children, newDir)

      return updatedDirs
    }
  }

  return directories
}

const defaultState = {
  filters: [],
  root: null,
  currentId: null,
  currentNode: null,
  currentConfiguration: null,
  directories: []
}

const baseReducer = {
  /**
   * A list of filters that needs to be applied to all the directories.
   */
  filters: makeInstanceReducer(defaultState.filters, {
    [EXPLORER_SET_FILTERS]: (state, action) => action.filters
  }),

  /**
   * The root of the explorer instance.
   *
   * The user will not be able to go higher in the directory structure
   * (most of the times it's used to store the WS root).
   */
  root: makeInstanceReducer(defaultState.root, {
    [EXPLORER_SET_ROOT]: (state, action) => action.root
  }),

  /**
   * The resource node ID of the current directory.
   *
   * NB. ID is stored in its own key, so I can load current directory config, summary, and ListData in //
   * Otherwise, I must wait the end of the ajax call to get the id in `current.node`
   */
  currentId: makeInstanceReducer(defaultState.currentId, {
    [EXPLORER_SET_CURRENT_ID]: (state, action) => action.currentId
  }),

  /**
   * The resource node of the current directory
   */
  currentNode: makeInstanceReducer(defaultState.currentNode, {
    [EXPLORER_SET_CURRENT_NODE]: (state, action) => action.current
  }),

  /**
   * The configuration of the current directory (aka the DirectoryResource).
   */
  currentConfiguration: makeInstanceReducer(defaultState.currentConfiguration, {
    [EXPLORER_SET_CURRENT_CONFIGURATION]: (state, action) => action.currentConfiguration
  }),

  /**
   * The list of available directories.
   *
   * NB. Each level is loaded on demand when the user uses directories nav,
   * so you can not assert this contains the full directories list.
   */
  directories: makeInstanceReducer([], {
    [EXPLORER_SET_ROOT]: (state, action) => action.root ? [action.root] : [],
    [DIRECTORIES_LOAD]: (state, action) => {
      if (!action.parentId) {
        return action.directories
      }

      const updatedParent = cloneDeep(selectors.directory(state, action.parentId))
      if (updatedParent) {
        // set parent children
        updatedParent._loaded = true
        updatedParent.children = action.directories

        return replaceDirectory(state, updatedParent)
      }

      return state
    },
    [DIRECTORY_TOGGLE_OPEN]: (state, action) => {
      const toToggle = cloneDeep(selectors.directory(state, action.directoryId))
      if (toToggle) {
        toToggle._opened = action.opened

        return replaceDirectory(state, toToggle)
      }

      return state
    },
    [DIRECTORY_UPDATE]: (state, action) => {
      const toUpdate = selectors.directory(state, action.updatedDirectory.id)

      if (toUpdate) {
        return replaceDirectory(state, merge(
          // we merge with previous state to keep loaded children if any
          {}, toUpdate, action.updatedDirectory
        ))
      }

      return state
    }
  })
}

/**
 * Creates reducers for resource explorer.
 *
 * @param {string} explorerName - the name of the explorer.
 * @param {object} initialState - the initial state of the explorer instance.
 * @param {object} customReducer - an object containing custom reducer.
 *
 * @return {function}
 */
function makeResourceExplorerReducer(explorerName, initialState = {}, customReducer = {}) {
  const explorerState = merge({}, defaultState, initialState)

  // enhances base explorer reducer with the ones defined by the user if any
  const final = Object
    .keys(baseReducer)
    .reduce((finalReducer, current) => {
      if (customReducer[current]) {
        // apply base and custom reducer to the store key
        finalReducer[current] = reduceReducers(baseReducer[current](explorerName, explorerState[current]), customReducer[current])
      } else {
        // we just need to add the standard reducer
        finalReducer[current] = baseReducer[current](explorerName, explorerState[current])
      }

      return finalReducer
    }, {})

  // get custom keys
  const rest = difference(Object.keys(customReducer), Object.keys(baseReducer))
  rest.map(reducerName =>
    final[reducerName] = customReducer[reducerName]
  )

  // add resources list reducer (I must declare it here to namespace list with explorerName)
  // It can't be extended by customReducer
  Object.assign(final, {
    /**
     * The list of resources for the current directory.
     */
    resources: makeListReducer(`${explorerName}.resources`, {}, {
      invalidated: makeReducer(false, {
        [`${EXPLORER_SET_CURRENT_ID}/${explorerName}`]: () => true
      }),
      selected: makeReducer(false, {
        [`${EXPLORER_SET_CURRENT_ID}/${explorerName}`]: () => []
      }),
      filters: makeReducer([], {
        [`${EXPLORER_SET_CURRENT_CONFIGURATION}/${explorerName}`]: (state, action) => {
          const explorerFilters = action.explorerFilters || []
          const directoryFilters = get(action.currentConfiguration, 'list.filters') || []

          return unionBy(explorerFilters, directoryFilters, (filter) => filter.property)
        }
      }),
      page: makeReducer([], {
        [`${EXPLORER_SET_CURRENT_CONFIGURATION}/${explorerName}`]: () => 0
      }),
      pageSize: makeReducer([], {
        [`${EXPLORER_SET_CURRENT_CONFIGURATION}/${explorerName}`]: (state, action) => get(action.currentConfiguration, 'list.pageSize') || listConst.DEFAULT_PAGE_SIZE
      }),
      sortBy: makeReducer([], {
        [`${EXPLORER_SET_CURRENT_CONFIGURATION}/${explorerName}`]: (state, action) => {
          const sorting = get(action.currentConfiguration, 'list.sorting')

          let sortBy = {property: null, direction: 0}
          if (sorting) {
            if (0 === sorting.indexOf('-')) {
              sortBy.property = sorting.replace('-', '') // replace first -
              sortBy.direction = -1
            } else {
              sortBy.property = sorting
              sortBy.direction = 1
            }
          }

          return sortBy
        }
      })
    })
  })

  return combineReducers(final)
}

export {
  makeResourceExplorerReducer
}
