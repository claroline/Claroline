import {makeLogReducer} from '#/main/core/layout/logs/reducer'
import {reducer as connectionsReducer} from '#/main/core/administration/logs/connection/store/reducer'

const reducer = makeLogReducer({}, {
  connections: connectionsReducer
})

export {reducer}
