import {selectors as baseSelectors} from '#/main/example/tools/example/store/selectors'

const STORE_NAME = baseSelectors.STORE_NAME + '.crud'

const LIST_NAME = STORE_NAME+ '.list'
const FORM_NAME = STORE_NAME + '.current'

export const selectors = {
  STORE_NAME,
  LIST_NAME,
  FORM_NAME
}
