import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {selectors} from '#/integration/big-blue-button/resources/bbb/store/selectors'

const reducer = makeFormReducer(selectors.STORE_NAME+'.bbbForm')

export {
  reducer
}
