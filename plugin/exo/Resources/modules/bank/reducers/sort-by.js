import {makeReducer} from '#/main/core/utilities/redux'

import {
  SORT_BY_UPDATE
} from './../actions/sort-by'

function updateSortBy(sortByState, action = {}) {
  let direction = 1
  if (sortByState.property === action.property) {
    if (1 === sortByState.direction) {
      direction = -1
    } else if (-1 === sortByState.direction) {
      direction = 0
    }
    else {
      direction = 1
    }
  }

  return {
    property: action.property,
    direction: direction
  }
}

const sortByReducer = makeReducer({
  property: null,
  direction: 0
}, {
  [SORT_BY_UPDATE]: updateSortBy
})

export default sortByReducer
