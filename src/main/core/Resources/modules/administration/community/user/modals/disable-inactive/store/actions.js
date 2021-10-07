import {API_REQUEST} from '#/main/app/api'
import {actions as listActions} from '#/main/app/content/list/store'

import {selectors as baseSelectors} from '#/main/core/administration/community/store'

export const actions = {}

actions.disableInactive = (lastLogin) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['apiv2_user_disable_inactive'],
    request: {
      method: 'PUT',
      body: JSON.stringify({lastLogin: lastLogin})
    },
    success: () => dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.users.list'))
  }
})
