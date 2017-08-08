import cloneDeep from 'lodash/cloneDeep'
import {makeReducer} from '#/main/core/utilities/redux'
import {
  BBB_URL_UPDATE,
  RESOURCE_FORM_INITIALIZE,
  RESOURCE_FORM_UPDATE,
  RESOURCE_INITIALIZE,
  CAN_JOIN_UPDATE,
  MESSAGE_RESET,
  MESSAGE_UPDATE
} from './actions'

const bbbReducers = makeReducer(null, {
  [BBB_URL_UPDATE]: (state, action) => action.url
})

const resourceFormReducers = makeReducer({}, {
  [RESOURCE_FORM_INITIALIZE]: (state, action) => action.state,
  [RESOURCE_FORM_UPDATE]: (state, action) => {
    const newState = cloneDeep(state)
    newState[action.property] = action.value

    return newState
  }
})

const resourceReducers = makeReducer({}, {
  [RESOURCE_INITIALIZE]: (state, action) => action.state
})

const mainReducers = makeReducer({}, {})

const canJoinReducers = makeReducer({}, {
  [CAN_JOIN_UPDATE]: (state, action) => action.value
})

const messageReducers = makeReducer({}, {
  [MESSAGE_RESET]: () => {
    return {
      content: null,
      type: null
    }
  },
  [MESSAGE_UPDATE]: (state, action) => {
    return {
      content: action.content,
      type: action.status
    }
  }
})

export {
  bbbReducers,
  resourceFormReducers,
  resourceReducers,
  mainReducers,
  canJoinReducers,
  messageReducers
}