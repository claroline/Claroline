import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {selectors} from '#/main/example/tools/example/modals/parameters/store/selectors'

const reducer = makeFormReducer(selectors.STORE_NAME, {
  new: true
})

export {
  reducer
}
