import {makeFormReducer} from '#/main/app/content/form/store/reducer'

const reducer = {
  textForm: makeFormReducer('textForm', {}, {})
}

export {
  reducer
}