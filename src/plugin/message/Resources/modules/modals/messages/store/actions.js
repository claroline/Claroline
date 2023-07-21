import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST, url} from '#/main/app/api'

import {constants} from '#/plugin/message/modals/messages/constants'

// actions
export const HEADER_MESSAGES_LOAD       = 'HEADER_MESSAGES_LOAD'
export const HEADER_MESSAGES_SET_LOADED = 'HEADER_MESSAGES_SET_LOADED'

// action creators
export const actions = {}

actions.load = makeActionCreator(HEADER_MESSAGES_LOAD, 'results')
actions.setLoaded = makeActionCreator(HEADER_MESSAGES_SET_LOADED, 'loaded')

actions.getMessages = () => (dispatch) => dispatch({
  [API_REQUEST]: {
    silent: true,
    url: url(['apiv2_message_list'], {
      page: 0,
      limit: constants.LIMIT_RESULTS,
      filters: {
        read: false,
        removed: false,
        sent: false
      },
      sortBy: '-meta.date'
    }),
    before: () => dispatch(actions.setLoaded(false)),
    success: (response) => dispatch(actions.load(response.data || []))
  }
})
