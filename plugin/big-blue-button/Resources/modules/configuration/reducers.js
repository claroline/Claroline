import cloneDeep from 'lodash/cloneDeep'
import {makeReducer} from '#/main/core/utilities/redux'
import {
  CONFIGURATION_UPDATE,
  CONFIGURATION_MESSAGE_RESET,
  CONFIGURATION_MESSAGE_UPDATE,
  MEETINGS_INIT
} from './actions'

const mainReducers =  makeReducer({}, {})

const configReducers =  makeReducer({}, {
  [CONFIGURATION_UPDATE]: (state, action) => {
    const newState = cloneDeep(state)
    newState[action.property] = action.value

    return newState
  }
})

const messageReducers = makeReducer({}, {
  [CONFIGURATION_MESSAGE_RESET]: () => {
    return {
      content: null,
      type: null
    }
  },
  [CONFIGURATION_MESSAGE_UPDATE]: (state, action) => {
    return {
      content: action.content,
      type: action.status
    }
  }
})

const meetingsReducers =  makeReducer([], {
  [MEETINGS_INIT]: (state, action) => {
    return action.meetings
  }
})

export {
  mainReducers,
  configReducers,
  messageReducers,
  meetingsReducers
}