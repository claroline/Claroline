import {makeReducer} from '#/main/app/store/reducer'

const reducer = {
  textFile: makeReducer({}, {}),
  isHtml: makeReducer(false, {}),
  content: makeReducer('', {})
}

export {
  reducer
}