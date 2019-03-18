import {makeListReducer} from '#/main/app/content/list/store'

const reducer = {
  questions: makeListReducer('questions')
}

export {
  reducer
}
