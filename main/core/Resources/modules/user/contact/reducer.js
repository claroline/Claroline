import {makeFormReducer} from '#/main/core/data/form/reducer'
import {makeListReducer} from '#/main/core/data/list/reducer'

const reducer = {
  options: makeFormReducer('options'),
  contacts: makeListReducer('contacts'),
  visibleUsers: makeListReducer('visibleUsers')
}

export {
  reducer
}