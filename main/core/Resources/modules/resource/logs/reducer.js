import {makeLogReducer} from '#/main/core/layout/logs/reducer'
import {makeReducer} from '#/main/core/scaffolding/reducer'

const reducer = makeLogReducer({}, {
  resourceId: makeReducer(null, {})
})

export {reducer}