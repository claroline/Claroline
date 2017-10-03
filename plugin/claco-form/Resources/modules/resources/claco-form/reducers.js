import cloneDeep from 'lodash/cloneDeep'
import {makeReducer} from '#/main/core/utilities/redux'
import {
  RESOURCE_PROPERTY_UPDATE,
  RESOURCE_PARAMS_PROPERTY_UPDATE,
  PARAMETERS_INITIALIZE,
  PARAMETERS_UPDATE
} from './editor/actions'
import {
  MESSAGE_RESET,
  MESSAGE_UPDATE
} from './actions'

const mainReducers = makeReducer({}, {})

const resourceReducers = makeReducer({}, {
  [RESOURCE_PROPERTY_UPDATE]: (state, action) => Object.assign({}, state, {[action.property]: action.value}),
  [RESOURCE_PARAMS_PROPERTY_UPDATE]: (state, action) => {
    const details = Object.assign({}, state.details, {[action.property]: action.value})

    return Object.assign({}, state, {details: details})
  }
})

const parametersReducers = makeReducer({}, {
  [PARAMETERS_INITIALIZE]: (state, action) => action.params,
  [PARAMETERS_UPDATE]: (state, action) => {
    const parameters = cloneDeep(state)
    parameters[action.property] = action.value

    return parameters
  }
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
  mainReducers,
  resourceReducers,
  parametersReducers,
  messageReducers
}