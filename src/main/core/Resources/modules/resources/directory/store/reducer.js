import cloneDeep from 'lodash/cloneDeep'
import get from 'lodash/get'

import {makeInstanceAction} from '#/main/app/store/actions'
import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'
import {parseSortBy} from '#/main/app/content/list/utils'
import {constants as listConst} from '#/main/app/content/list/constants'
import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

import {DIRECTORIES_LOAD, DIRECTORY_TOGGLE_OPEN} from '#/main/core/resources/directory/store/actions'
import {selectors} from '#/main/core/resources/directory/store/selectors'

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

const reducer = combineReducers({
  resource: makeReducer({}, {
    [makeInstanceAction(RESOURCE_LOAD, 'directory')]: (state, action) => action.resourceData.resource
  }),

  storageLock: makeReducer(false, {
    [makeInstanceAction(RESOURCE_LOAD, 'directory')]: (state, action) => action.resourceData.storageLock
  }),

  /**
   * The list of available directories.
   *
   * NB. Each level is loaded on demand when the user uses directories nav,
   * so you can not assert this contains the full directories list.
   */
  directories: makeReducer([], {
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
    }
  }),

  /**
   * The list of the resources of the current directory.
   */
  resources: makeListReducer(selectors.LIST_NAME, {}, {
    invalidated: makeReducer(false, {
      [makeInstanceAction(RESOURCE_LOAD, 'directory')]: () => true
    }),
    selected: makeReducer([], {
      [makeInstanceAction(RESOURCE_LOAD, 'directory')]: () => []
    }),
    filters: makeReducer([], {
      [makeInstanceAction(RESOURCE_LOAD, 'directory')]: (state, action) => get(action.resourceData.resource, 'list.filters') || []
    }),
    pagination: combineReducers({
      page: makeReducer([], {
        [makeInstanceAction(RESOURCE_LOAD, 'directory')]: () => 0
      }),
      pageSize: makeReducer([], {
        [makeInstanceAction(RESOURCE_LOAD, 'directory')]: (state, action) => get(action.resourceData.resource, 'list.pageSize') || listConst.DEFAULT_PAGE_SIZE
      })
    }),
    sortBy: makeReducer([], {
      [makeInstanceAction(RESOURCE_LOAD, 'directory')]: (state, action) => parseSortBy(get(action.resourceData.resource, 'list.sorting', null))
    })
  })
})

export {
  reducer
}
