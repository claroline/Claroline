import {makeLogReducer} from '#/main/core/layout/logs/reducer'
import {makeReducer} from '#/main/app/store/reducer'

const reducer = makeLogReducer({}, {
  workspaceId: makeReducer(null, {})
})

export {reducer}