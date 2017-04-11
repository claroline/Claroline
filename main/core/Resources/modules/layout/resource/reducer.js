import {makeReducer} from '#/main/core/utilities/redux'
import {
  RESOURCE_PUBLICATION_TOGGLE
} from './actions'

function togglePublication(currentState) {
  return Object.assign({}, currentState, {
    meta: Object.assign({}, currentState.meta, {
      published: !currentState.meta.published
    })
  })
}

const reducer = makeReducer({}, {
  [RESOURCE_PUBLICATION_TOGGLE]: togglePublication
})

export {reducer}
