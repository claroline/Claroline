import {API_REQUEST, url} from '#/main/app/api'
import {actions as listActions} from '#/main/app/content/list/store'

import {selectors as dashboardSelectors} from '#/plugin/analytics/resource/dashboard/store/selectors'

const actions = {}

actions.createRequirements = (resourceId, objects, type) => (dispatch) => {
  dispatch({
    [API_REQUEST]: {
      url: url(['apiv2_workspace_requirements_resource_update', {resourceNode: resourceId, type: type}], {ids: objects.map(o => o.id)}),
      request: {
        method: 'PUT'
      },
      success: (response, dispatch) => {
        switch (type) {
          case 'role':
            dispatch(listActions.invalidateData(dashboardSelectors.STORE_NAME + '.requirements.roles'))
            break
          case 'user':
            dispatch(listActions.invalidateData(dashboardSelectors.STORE_NAME + '.requirements.users'))
            break
        }
      }
    }
  })
}

export {
  actions
}
