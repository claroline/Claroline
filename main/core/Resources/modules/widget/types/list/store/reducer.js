import {makeListReducer} from '#/main/app/content/list/store'

function makeListWidgetReducer(storeName, defaultState) {
  return makeListReducer(storeName, defaultState)
}

export {
  makeListWidgetReducer
}
