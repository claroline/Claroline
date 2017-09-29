import {makeActionCreator} from '#/main/core/utilities/redux'

import {REQUEST_SEND} from '#/main/core/api/actions'
import {select as listSelect} from '#/main/core/layout/list/selectors'

export const actions = {}

// filters
export const LIST_FILTER_ADD    = 'LIST_FILTER_ADD'
export const LIST_FILTER_REMOVE = 'LIST_FILTER_REMOVE'

actions.addFilter    = makeActionCreator(LIST_FILTER_ADD, 'property', 'value')
actions.removeFilter = makeActionCreator(LIST_FILTER_REMOVE, 'filter')


// sorting
export const LIST_SORT_UPDATE = 'LIST_SORT_UPDATE'

actions.updateSort = makeActionCreator(LIST_SORT_UPDATE, 'property')


// selection
export const LIST_RESET_SELECT      = 'LIST_RESET_SELECT'
export const LIST_TOGGLE_SELECT     = 'LIST_TOGGLE_SELECT'
export const LIST_TOGGLE_SELECT_ALL = 'LIST_TOGGLE_SELECT_ALL'

actions.resetSelect     = makeActionCreator(LIST_RESET_SELECT)
actions.toggleSelect    = makeActionCreator(LIST_TOGGLE_SELECT, 'row')
actions.toggleSelectAll = makeActionCreator(LIST_TOGGLE_SELECT_ALL, 'rows')


// data loading
export const LIST_DATA_LOAD = 'LIST_DATA_LOAD'

actions.loadData = makeActionCreator(LIST_DATA_LOAD, 'data', 'total')
actions.fetchData = (name) => (dispatch, getState) => {
  const listState = getState()[name]

  dispatch({
    [REQUEST_SEND]: {
      url: listSelect.fetchUrl(listState) + listSelect.queryString(listState),
      request: {
        method: 'GET'
      },
      success: (response, dispatch) => {
        dispatch(actions.resetSelect())
        dispatch(actions.loadData(response.data, response.totalResults))
      }
    }
  })
}


// pagination
export const LIST_PAGE_SIZE_UPDATE = 'LIST_PAGE_SIZE_UPDATE'
export const LIST_PAGE_CHANGE      = 'LIST_PAGE_CHANGE'

actions.changePage     = makeActionCreator(LIST_PAGE_CHANGE, 'page')
actions.updatePageSize = makeActionCreator(LIST_PAGE_SIZE_UPDATE, 'pageSize')
