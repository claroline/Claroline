import cloneDeep from 'lodash/cloneDeep'
import set from 'lodash/set'

import {makeReducer} from '#/main/core/scaffolding/reducer'
import {makeListReducer} from '#/main/core/data/list/reducer'
import {reducer as modalReducer} from '#/main/core/layout/modal/reducer'

import {validate} from '#/plugin/drop-zone/plugin/configuration/validator'

import {
  TOOL_FORM_LOAD,
  TOOL_FORM_RESET,
  TOOL_FORM_UPDATE,
  TOOL_FORM_VALIDATE,
  TOOL_UPDATE
} from './actions'

const toolFormReducer = makeReducer({
  validating: false,
  pendingChanges: false,
  errors: {},
  data: null
}, {
  [TOOL_FORM_LOAD]: (state, action) => ({
    validating: false,
    pendingChanges: false,
    errors: {},
    data: action.tool
  }),
  [TOOL_FORM_RESET]: () => ({
    validating: false,
    pendingChanges: false,
    errors: {},
    data: null
  }),
  [TOOL_FORM_UPDATE]: (state, action) => {
    const newData = cloneDeep(state.data)
    set(newData, action.property, action.value)

    return {
      validating: false,
      pendingChanges: true,
      errors: validate(newData),
      data: newData
    }
  },
  [TOOL_FORM_VALIDATE]: (state) => ({
    validating: true,
    pendingChanges: state.pendingChanges,
    errors: validate(state.data),
    data: state.data
  })
})

const toolsReducer = makeReducer({}, {
  [TOOL_UPDATE]: (state, action) => {
    const tools = cloneDeep(state)
    const index = tools.findIndex(t => t.id === action.tool.id)

    if (index > -1) {
      tools[index] = action.tool
    } else {
      tools.push(action.tool)
    }

    return tools
  }
})

const toolsTotalResultsReducer = makeReducer({}, {
  [TOOL_UPDATE]: (state) => {
    return state ? state : 1
  }
})

const reducer = {
  toolForm: toolFormReducer,
  tools: makeListReducer('tools', {}, {data: toolsReducer, totalResults: toolsTotalResultsReducer}),
  modal: modalReducer
}

export {
  reducer
}
