import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'

const reducer = {
  options: makeFormReducer('options'),
  contacts: makeListReducer('contacts'),
  visibleUsers: makeListReducer('visibleUsers')
}

export {
  reducer
}