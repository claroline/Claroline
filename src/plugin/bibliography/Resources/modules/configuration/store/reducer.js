import {makeFormReducer} from '#/main/app/content/form/store/reducer'


const reducer = {
  bookReferenceConfiguration: makeFormReducer('bookReferenceConfiguration')
}

export {
  reducer
}