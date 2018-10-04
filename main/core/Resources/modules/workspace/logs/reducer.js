import {makeReducer} from '#/main/app/store/reducer'

import {makeLogReducer} from '#/main/core/layout/logs/reducer'
import {reducer as connectionsReducer} from '#/main/core/workspace/logs/connection/store/reducer'


const reducer = makeLogReducer({}, {
  workspaceId: makeReducer(null, {}),
  connections: connectionsReducer
})

export {reducer}