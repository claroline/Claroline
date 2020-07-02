import {API_REQUEST} from '#/main/app/api'
import {actions as listActions} from '#/main/app/content/list/store'

import {selectors} from '#/plugin/cursus/administration/cursus/store/selectors'

export const actions = {}

actions.validate = (queueId) => ({
  [API_REQUEST]: {
    url: ['apiv2_cursus_session_validate_queue', {queue: queueId}],
    request: {
      method: 'PUT'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(selectors.STORE_NAME + '.queues.list'))
    }
  }
})