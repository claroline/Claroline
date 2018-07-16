import {makeFormReducer} from '#/main/core/data/form/reducer'

import {selectors} from '#/main/core/widget/content/modals/parameters/store/selectors'


const reducer = makeFormReducer(selectors.STORE_NAME, {})

export {
  reducer
}
