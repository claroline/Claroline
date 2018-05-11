import {makeReducer} from '#/main/core/scaffolding/reducer'
import {makeFormReducer} from '#/main/core/data/form/reducer'
import {makePageReducer} from '#/main/core/layout/page/reducer'
import {makeListReducer} from '#/main/core/data/list/reducer'
import {reducer as logReducer} from '#/main/core/administration/transfer/components/log/reducer'

const reducer = makePageReducer({}, {
  explanation: makeReducer({}, {}),
  import: makeFormReducer('import'),
  export: makeFormReducer('export'),
  history: makeListReducer('history', {}, {
    invalidated: makeReducer(false, {
    })
  }),
  log: logReducer
})

export {
  reducer
}
