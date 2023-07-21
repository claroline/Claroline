import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'

// actions
export const HEADER_MESSAGES_COUNT = 'HEADER_MESSAGES_COUNT'

// action creators
export const actions = {}

actions.setCount = makeActionCreator(HEADER_MESSAGES_COUNT, 'count')

actions.countMessages = () => (dispatch) => dispatch({
  [API_REQUEST]: {
    silent: true,
    url: ['apiv2_message_count_unread'],
    success: (response) => dispatch(actions.setCount(response))
  }
})
