import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {reducer as tokenReducer} from '#/main/core/tools/desktop-parameters/token/store/reducer'
import {makeReducer} from '#/main/app/store/reducer'

const reducer = {
  tools: makeReducer(),
  toolsConfig: makeFormReducer('toolsConfig'),
  tokens: tokenReducer
}

export {
  reducer
}
