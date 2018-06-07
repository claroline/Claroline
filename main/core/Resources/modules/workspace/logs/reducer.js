import {makeLogReducer} from '#/main/core/layout/logs/reducer'
import {makeReducer} from '#/main/core/scaffolding/reducer'

const reducer = makeLogReducer({}, {
  workspaceId: makeReducer(null, {})
})

export {reducer}