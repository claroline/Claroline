import {makePageReducer} from '#/main/core/layout/page/reducer'
import {makeFormReducer} from '#/main/core/data/form/reducer'
import {makeListReducer} from '#/main/core/data/list/reducer'

const reducer = makePageReducer({}, {
  options: makeFormReducer('options'),
  contacts: makeListReducer('contacts'),
  visibleUsers: makeListReducer('visibleUsers')
})

export {
  reducer
}