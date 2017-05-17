import {makeActionCreator} from '#/main/core/utilities/redux'
import {generateUrl} from '#/main/core/fos-js-router'

import {actions as listActions} from '#/main/core/layout/list/actions'
import {actions as paginationActions} from '#/main/core/layout/pagination/actions'
import {select as listSelect} from '#/main/core/layout/list/selectors'
import {select as paginationSelect} from '#/main/core/layout/pagination/selectors'

import {REQUEST_SEND} from '#/main/core/api/actions'

export const WORKSPACES_LOAD = 'WORKSPACES_LOAD'

export const actions = {}

actions.loadWorkspaces = makeActionCreator(WORKSPACES_LOAD, 'workspaces', 'total')

actions.removeWorkspaces = (workspaces) => ({
  [REQUEST_SEND]: {
    url: generateUrl('api_delete_workspace') + workspaceQueryString(workspaces),
    request: {
      method: 'DELETE'
    },
    success: (data, dispatch) => {
      //do something better
      dispatch(paginationActions.changePage(0))
      dispatch(actions.fetchWorkspaces())
    }
  }
})

actions.copyWorkspaces = (workspaces, isModel = 0) => ({
  [REQUEST_SEND]: {
    url: generateUrl('api_copy_workspaces', {isModel: isModel}) + workspaceQueryString(workspaces),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => dispatch(actions.fetchWorkspaces())
  }
})

actions.fetchWorkspaces = () => (dispatch, getState) => {
  const state = getState()

  const page = paginationSelect.current(state)
  const pageSize = paginationSelect.pageSize(state)
  let url = generateUrl('api_get_search_workspaces', {page: page, limit: pageSize}) + '?'

  // build queryString
  let queryString = ''

  // add filters
  const filters = listSelect.filters(state)
  if (0 < filters.length) {
    queryString += filters.map(filter => `filters[${filter.property}]=${filter.value}`).join('&')
  }

  // add sort by
  const sortBy = listSelect.sortBy(state)
  if (sortBy.property && 0 !== sortBy.direction) {
    queryString += `${0 < queryString.length ? '&':''}sortBy=${-1 === sortBy.direction ? '-':''}${sortBy.property}`
  }

  dispatch({
    [REQUEST_SEND]: {
      url: url + queryString,
      request: {
        method: 'GET'
      },
      success: (data, dispatch) => {
        dispatch(listActions.resetSelect())
        dispatch(actions.loadWorkspaces(data.workspaces, data.total))
      }
    }
  })
}

const workspaceQueryString = (workspaces) => '?' + workspaces.map(workspace => 'ids[]='+workspace.id).join('&')
