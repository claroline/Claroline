import {url} from '#/main/app/api'

import {API_REQUEST} from '#/main/app/api'
import {actions as listActions} from '#/main/app/content/list/store'
import {selectors}  from '#/plugin/open-badge/tools/badges/store/selectors'

export const actions = {}

actions.enable = (badges) => ({
  [API_REQUEST]: {
    url: url(['apiv2_badge-class_enable'], {ids: badges.map(u => u.id)}),
    request: {
      method: 'PUT'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(selectors.STORE_NAME +'.badges.list'))
    }
  }
})

actions.disable = (badges) => ({
  [API_REQUEST]: {
    url: url(['apiv2_badge-class_disable'], {ids: badges.map(u => u.id)}),
    request: {
      method: 'PUT'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(selectors.STORE_NAME +'.badges.list'))
    }
  }
})
