import {makeReducer} from '#/main/core/utilities/redux/reducer'
import {makePageReducer} from '#/main/core/layout/page/reducer'

const reducer = makePageReducer({}, {
  user: makeReducer({}, {

  })
})

export {
  reducer
}
