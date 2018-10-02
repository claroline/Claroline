import {reducer as connectionsReducer} from '#/main/core/administration/logs/connection/store/reducer'
import {makeLogReducer} from '#/main/core/layout/logs/reducer'

const reducer = makeLogReducer({}, {})
reducer['connections'] = connectionsReducer

export {reducer}
