import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST, url} from '#/main/app/api'

import {constants} from '#/plugin/message/header/messages/constants'

// actions
export const HEADER_MESSAGES_LOAD       = 'HEADER_MESSAGES_LOAD'
export const HEADER_MESSAGES_SET_LOADED = 'HEADER_MESSAGES_SET_LOADED'
export const HEADER_MESSAGES_COUNT      = 'HEADER_MESSAGES_COUNT'

// action creators
export const actions = {}

actions.setCount = makeActionCreator(HEADER_MESSAGES_COUNT, 'count')
actions.load = makeActionCreator(HEADER_MESSAGES_LOAD, 'results')
actions.setLoaded = makeActionCreator(HEADER_MESSAGES_SET_LOADED, 'loaded')

actions.getMessages = () => ({
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
    before: (dispatch) => dispatch(actions.setLoaded(false)),
    success: (response, dispatch) => {
      dispatch(actions.load(response.data || []))
      dispatch(actions.setCount(response.totalResults))
    }
  }
})

actions.countMessages = () => ({
  [API_REQUEST]: {
    silent: true,
    url: ['apiv2_message_count_unread'],
    success: (response, dispatch) => dispatch(actions.setCount(response))
  }
})
