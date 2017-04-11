import {makeReducer} from '#/main/core/utilities/redux'

import {
  OBJECT_SELECT
} from './../actions/current-object'

function selectObject(currentObjectState, action = {}) {
  return {
    id: action.id,
    type: action.type
  }
}

const currentObjectReducer = makeReducer({}, {
  [OBJECT_SELECT]: selectObject
})

export default currentObjectReducer
