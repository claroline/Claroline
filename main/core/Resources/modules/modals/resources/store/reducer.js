import get from 'lodash/get'
import unionBy from 'lodash/unionBy'

import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'
import {constants as listConst} from '#/main/app/content/list/constants'

import {
  EXPLORER_SET_LOADING,
  EXPLORER_SET_ROOT,
  EXPLORER_SET_CURRENT_ID,
  EXPLORER_SET_CURRENT_NODE,
  EXPLORER_SET_CURRENT_CONFIGURATION,
  EXPLORER_SET_FILTERS
} from '#/main/core/modals/resources/store/actions'
import {selectors} from '#/main/core/modals/resources/store/selectors'

const defaultState = {
  loading: false,
  filters: [],
  root: null,
  currentId: null,
  currentNode: null,
  currentConfiguration: null
}

const reducer = combineReducers({
  loading: makeReducer(defaultState.loading, {
    [EXPLORER_SET_LOADING]: (state, action) => action.loading
  }),

  /**
   * A list of filters that needs to be applied to all the directories.
   */
  filters: makeReducer(defaultState.filters, {
    [EXPLORER_SET_FILTERS]: (state, action) => action.filters
  }),

  /**
   * The root of the explorer instance.
   *
   * The user will not be able to go higher in the directory structure
   * (most of the times it's used to store the WS root).
   */
  root: makeReducer(defaultState.root, {
    [EXPLORER_SET_ROOT]: (state, action) => action.root
  }),

  /**
   * The resource node ID of the current directory.
   *
   * NB. ID is stored in its own key, so I can load current directory config, summary, and ListData in //
   * Otherwise, I must wait the end of the ajax call to get the id in `current.node`
   */
  currentId: makeReducer(defaultState.currentId, {
    [EXPLORER_SET_CURRENT_ID]: (state, action) => action.currentId
  }),

  /**
   * The resource node of the current directory
   */
  currentNode: makeReducer(defaultState.currentNode, {
    [EXPLORER_SET_CURRENT_NODE]: (state, action) => action.current
  }),

  /**
   * The configuration of the current directory (aka the DirectoryResource).
   */
  currentConfiguration: makeReducer(defaultState.currentConfiguration, {
    [EXPLORER_SET_CURRENT_CONFIGURATION]: (state, action) => action.currentConfiguration
  }),

  /**
   * The list of resources for the current directory.
   */
  resources: makeListReducer(`${selectors.STORE_NAME}.resources`, {}, {
    selected: makeReducer([], {
      [EXPLORER_SET_CURRENT_ID]: () => []
    }),
    filters: makeReducer([], {
      [EXPLORER_SET_CURRENT_CONFIGURATION]: (state, action) => {
        const explorerFilters = action.explorerFilters || []
        const directoryFilters = get(action.currentConfiguration, 'list.filters') || []

        return unionBy(explorerFilters, directoryFilters, (filter) => filter.property)
      }
    }),
    page: makeReducer([], {
      [EXPLORER_SET_CURRENT_CONFIGURATION]: () => 0
    }),
    pageSize: makeReducer([], {
      [EXPLORER_SET_CURRENT_CONFIGURATION]: (state, action) => get(action.currentConfiguration, 'list.pageSize') || listConst.DEFAULT_PAGE_SIZE
    }),
    sortBy: makeReducer([], {
      [EXPLORER_SET_CURRENT_CONFIGURATION]: (state, action) => {
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

export {
  reducer
}
