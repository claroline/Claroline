import {makeListReducer} from '#/main/app/content/list/store'

const reducer = {
  resources: makeListReducer('resources', {})
}

export {
  reducer
}
