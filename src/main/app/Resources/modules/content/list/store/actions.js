import {url} from '#/main/app/api'
import {makeInstanceActionCreator} from '#/main/app/store/actions'

import {API_REQUEST} from '#/main/app/api'
import {actions as paginationActions} from '#/main/app/content/pagination/store/actions'
import {actions as searchActions} from '#/main/app/content/search/store/actions'

import {selectors} from '#/main/app/content/list/store/selectors'

export const actions = {}

// filters
actions.updateText = searchActions.updateText
actions.addFilter = searchActions.addFilter
actions.removeFilter = searchActions.removeFilter
actions.resetFilters = searchActions.resetFilters

// pagination
actions.changePage     = paginationActions.changePage
actions.updatePageSize = paginationActions.updatePageSize


// sorting
export const LIST_SORT_UPDATE = 'LIST_SORT_UPDATE'

actions.updateSort = makeInstanceActionCreator(LIST_SORT_UPDATE, 'property', 'direction')

// selection
export const LIST_RESET_SELECT      = 'LIST_RESET_SELECT'
export const LIST_TOGGLE_SELECT     = 'LIST_TOGGLE_SELECT'
export const LIST_TOGGLE_SELECT_ALL = 'LIST_TOGGLE_SELECT_ALL'

actions.resetSelect     = makeInstanceActionCreator(LIST_RESET_SELECT)
actions.toggleSelect    = makeInstanceActionCreator(LIST_TOGGLE_SELECT, 'row')
actions.toggleSelectAll = makeInstanceActionCreator(LIST_TOGGLE_SELECT_ALL, 'rows')


// data loading
export const LIST_DATA_LOAD       = 'LIST_DATA_LOAD'
export const LIST_DATA_INVALIDATE = 'LIST_DATA_INVALIDATE'
export const LIST_SET_ERROR = 'LIST_SET_ERROR'

actions.setError = makeInstanceActionCreator(LIST_SET_ERROR, 'code', 'message', 'additional')
actions.loadData = makeInstanceActionCreator(LIST_DATA_LOAD, 'data', 'total')
actions.invalidateData = makeInstanceActionCreator(LIST_DATA_INVALIDATE)
actions.fetchData = (listName, target, invalidate = false) => (dispatch, getState) => {
  const listState = selectors.list(getState(), listName)

  if (invalidate) {
    dispatch(actions.invalidateData(listName))
  }

  return dispatch({
    [API_REQUEST]: {
      silent: true,
      silentError: true,
      url: url(target) + selectors.queryString(listState),
      success: (response, dispatch) => {
        dispatch(actions.loadData(listName, response.data, response.totalResults))
      },
      error: (response, status) => {
        switch (status) {
          case 404:
            dispatch(actions.setError(listName, 'NOT_FOUND', 'List not found.', null))
            break
          case 500:
            dispatch(actions.setError(listName, 'UNKNOWN_ERROR', response.message, response.trace))
            break
          case 401:
          case 403:
            console.log('coucou')
            dispatch(actions.setError(listName, 'NO_RIGHTS', 'You don\'t have the rights to open this list.', null))
            break
        }
      }
    }
  })
}


// data delete
export const LIST_DATA_DELETE = 'LIST_DATA_DELETE'

actions.deleteItems = makeInstanceActionCreator(LIST_DATA_DELETE, 'items')
actions.deleteData = (listName, target, items) => ({
  [API_REQUEST]: {
    url: target,
    request: {
      method: 'DELETE',
      body: JSON.stringify(items.map(item => item.id))
    },
    success: (data, dispatch) => {
      dispatch(actions.deleteItems(listName, items))
      dispatch(actions.invalidateData(listName))
    }
  }
})
