import {makeReducer} from '#/main/app/store/reducer'

import {makeLogReducer} from '#/main/core/layout/logs/reducer'
import {reducer as connectionsReducer} from '#/main/core/resource/logs/connection/store/reducer'

const reducer = makeLogReducer({}, {
  resourceId: makeReducer(null, {}),
  connections: connectionsReducer
})

export {reducer}