import {makeFormReducer} from '#/main/app/content/form/store'

import {selectors} from '#/plugin/slideshow/resources/slideshow/editor/modals/slide/store/selectors'

const reducer = makeFormReducer(selectors.STORE_NAME)

export {
  reducer
}
