import cloneDeep from 'lodash/cloneDeep'
import {makeReducer} from '#/main/core/utilities/redux'
import {
  FIELD_ADD,
  FIELD_UPDATE,
  FIELD_REMOVE
} from './actions'

const fieldReducers = makeReducer({}, {
  [FIELD_ADD]: (state, action) => {
    const fields = cloneDeep(state)
    fields.push(action.field)

    return fields
  },
  [FIELD_UPDATE]: (state, action) => {
    const fields = cloneDeep(state)
    const index = fields.findIndex(f => f.id === action.field.id)

    if (index >= 0) {
      fields[index] = action.field
    }

    return fields
  },
  [FIELD_REMOVE]: (state, action) => {
    const fields = cloneDeep(state)
    const index = fields.findIndex(f => f.id === action.fieldId)

    if (index >= 0) {
      fields.splice(index, 1)
    }

    return fields
  }
})

export {
  fieldReducers
}