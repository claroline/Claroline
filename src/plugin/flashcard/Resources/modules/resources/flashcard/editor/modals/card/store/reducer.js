import {makeFormReducer} from '#/main/app/content/form/store'

import {selectors} from '#/plugin/flashcard/resources/flashcard/editor/modals/card/store/selectors'

const reducer = makeFormReducer(selectors.STORE_NAME)

export {
  reducer
}
