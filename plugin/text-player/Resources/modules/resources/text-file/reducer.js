import {makeReducer} from '#/main/core/scaffolding/reducer'

const reducer = {
  textFile: makeReducer({}, {}),
  isHtml: makeReducer(false, {}),
  content: makeReducer('', {})
}

export {
  reducer
}