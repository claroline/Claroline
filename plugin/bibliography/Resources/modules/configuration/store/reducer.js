import {makeFormReducer} from '#/main/core/data/form/reducer'


const reducer = {
  bookReferenceConfiguration: makeFormReducer('bookReferenceConfiguration')
}

export {
  reducer
}