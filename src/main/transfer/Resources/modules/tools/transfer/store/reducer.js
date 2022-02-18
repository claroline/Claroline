import {combineReducers} from '#/main/app/store/reducer'

import {reducer as logReducer} from '#/main/transfer/tools/transfer/log/store/reducer'
import {reducer as importReducer} from '#/main/transfer/tools/transfer/import/store/reducer'
import {reducer as exportReducer} from '#/main/transfer/tools/transfer/export/store/reducer'

const reducer = combineReducers({
  import: importReducer,
  export: exportReducer,
  log: logReducer
})

export {
  reducer
}
