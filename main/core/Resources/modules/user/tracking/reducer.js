import {makeReducer} from '#/main/core/scaffolding/reducer'
import {makePageReducer} from '#/main/core/layout/page/reducer'

const reducer = makePageReducer({}, {
  user: makeReducer({}, {

  })
})

export {
  reducer
}
