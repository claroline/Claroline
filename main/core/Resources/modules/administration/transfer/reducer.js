import {makeReducer} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/core/data/form/reducer'
import {makeListReducer} from '#/main/core/data/list/reducer'
import {reducer as logReducer} from '#/main/core/administration/transfer/log/reducer'

const reducer = {
  explanation: makeReducer({}, {}),
  import: makeFormReducer('import'),
  export: makeFormReducer('export'),
  history: makeListReducer('history', {}, {
    invalidated: makeReducer(false, {
    })
  }),
  log: logReducer
}

export {
  reducer
}
