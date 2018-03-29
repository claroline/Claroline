import {makeFormReducer} from '#/main/core/data/form/reducer'

const reducer = {
  textForm: makeFormReducer('textForm', {}, {})
}

export {
  reducer
}