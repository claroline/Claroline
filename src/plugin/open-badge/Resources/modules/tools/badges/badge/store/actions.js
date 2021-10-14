import {url} from '#/main/app/api'

import {API_REQUEST} from '#/main/app/api'
import {actions as listActions} from '#/main/app/content/list/store'
import {selectors} from '#/plugin/open-badge/tools/badges/badge/store/selectors'

export const actions = {}

actions.delete = (badges) => ({
  [API_REQUEST]: {
    url: url(['apiv2_badge-class_delete_bulk'], {ids: badges.map(u => u.id)}),
    request: {
      method: 'DELETE'
    },
    success: (data, dispatch) => dispatch(listActions.invalidateData(selectors.LIST_NAME))
  }
})

actions.enable = (badges) => ({
  [API_REQUEST]: {
    url: url(['apiv2_badge-class_enable'], {ids: badges.map(u => u.id)}),
    request: {
      method: 'PUT'
    },
    success: (data, dispatch) => dispatch(listActions.invalidateData(selectors.LIST_NAME))
  }
})

actions.disable = (badges) => ({
  [API_REQUEST]: {
    url: url(['apiv2_badge-class_disable'], {ids: badges.map(u => u.id)}),
    request: {
      method: 'PUT'
    },
    success: (data, dispatch) => dispatch(listActions.invalidateData(selectors.LIST_NAME))
  }
})

actions.grant = (badgeId, users) => ({
  [API_REQUEST]: {
    url: url(['apiv2_badge-class_add_users', {badge: badgeId}], {ids: users.map(user => user.id)}),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(selectors.LIST_NAME))
      dispatch(listActions.invalidateData(selectors.FORM_NAME + '.assertions'))
    }
  }
})

actions.recalculate = (badgeId) => ({
  [API_REQUEST]: {
    url: ['apiv2_badge-class_recalculate_users', {badge: badgeId}],
    request: {
      method: 'POST'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(selectors.LIST_NAME))
      dispatch(listActions.invalidateData(selectors.FORM_NAME + '.assertions'))
    }
  }
})