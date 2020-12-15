import {url} from '#/main/app/api/router'

import {API_REQUEST} from '#/main/app/api'
import {actions as listActions} from '#/main/app/content/list/store/actions'
import {actions as formActions} from '#/main/app/content/form/store/actions'

import {selectors as baseSelectors} from '#/main/core/administration/community/store'
import {User as UserTypes} from '#/main/core/user/prop-types'

export const USER_COMPARE = 'USER_COMPARE'

export const actions = {}

actions.open = (formName, id = null) => {
  if (id) {
    return {
      [API_REQUEST]: {
        url: ['apiv2_user_get', {id}],
        success: (response, dispatch) => dispatch(formActions.resetForm(formName, response, false))
      }
    }
  }

  return formActions.resetForm(formName, UserTypes.defaultProps, true)
}

actions.close = (formName) => formActions.resetForm(formName)

actions.compareOpen = (data) => ({
  type: USER_COMPARE,
  data: data
})

actions.compare = (ids) => {
  const queryParams = []

  ids.map((id, index) => {
    queryParams.push(`filters[id][${index}]=${id}`)
  })

  return {
    [API_REQUEST]: {
      url: url(['apiv2_user_list']) + '?' + queryParams.join('&'),
      success: (response, dispatch) => dispatch(actions.compareOpen(response.data))
    }
  }
}

actions.addGroups = (id, groups) => ({
  [API_REQUEST]: {
    url: url(['apiv2_user_add_groups', {id: id}], {ids: groups}),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.users.list'))
      dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.users.current.groups'))
    }
  }
})

actions.addRoles = (id, roles) => ({
  [API_REQUEST]: {
    url: url(['apiv2_user_add_roles', {id: id}], {ids: roles}),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.users.list'))
      dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.users.current.roles'))
    }
  }
})

actions.addOrganizations = (id, organizations) => ({
  [API_REQUEST]: {
    url: url(['apiv2_user_add_organizations', {id: id}], {ids: organizations}),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.users.list'))
      dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.users.current.organizations'))
    }
  }
})

actions.merge = (id1, id2) => ({
  [API_REQUEST]: {
    url: ['apiv2_user_merge', {keep: id1, remove: id2}],
    request: {method: 'PUT'},
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.users.list'))
      dispatch(listActions.resetSelect(baseSelectors.STORE_NAME+'.users.list'))
    }
  }
})