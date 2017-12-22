
import {makePageReducer} from '#/main/core/layout/page/reducer'
import {makeFormReducer} from '#/main/core/data/form/reducer'

export const reducer = makePageReducer({}, {
  termOfService: (state = null) => state,
  facets: (state = []) => state,
  options: (state = {}) => state,
  user: makeFormReducer('user', {
    new: true
  })
})
