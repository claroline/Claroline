import {makePageReducer} from '#/main/core/layout/page/reducer'
import {makeListReducer} from '#/main/core/data/list/reducer'

const reducer = makePageReducer({}, {
  portal: makeListReducer('portal', {}, {}, {
    selectable: false
  })
})

export {
  reducer
}
