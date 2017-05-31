import merge from 'lodash/merge'

import {makeReducer} from '#/main/core/utilities/redux'
import {
  RESOURCE_UPDATE_PUBLICATION,
  RESOURCE_UPDATE_NODE
} from './actions'

function togglePublication(currentState) {
  return merge({}, currentState, {
    meta: {
      published: !currentState.meta.published
    }
  })
}

function updateNode(currentState, action) {
  return merge({}, currentState, action.resourceNode)
}

const reducer = makeReducer({}, {
  [RESOURCE_UPDATE_PUBLICATION]: togglePublication,
  [RESOURCE_UPDATE_NODE]: updateNode
})

export {reducer}
