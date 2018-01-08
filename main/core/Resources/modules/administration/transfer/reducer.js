import {makeReducer} from '#/main/core/scaffolding/reducer'
import {makeFormReducer} from '#/main/core/data/form/reducer'
import {makePageReducer} from '#/main/core/layout/page/reducer'

const reducer = makePageReducer({}, {
  explanation: makeReducer({}, {}),
  import: makeFormReducer('import'),
  export: makeFormReducer('export')
})

export {
  reducer
}
