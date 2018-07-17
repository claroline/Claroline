import {makeLogReducer} from '#/main/core/layout/logs/reducer'
import {makeReducer} from '#/main/app/store/reducer'

const reducer = makeLogReducer({}, {
  resourceId: makeReducer(null, {})
})

export {reducer}