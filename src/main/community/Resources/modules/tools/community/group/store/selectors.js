import {selectors as baseSelectors} from '#/main/community/tools/community/store/selectors'

const LIST_NAME = baseSelectors.STORE_NAME + '.groups.list'
const FORM_NAME = baseSelectors.STORE_NAME + '.groups.current'

export const selectors = {
  LIST_NAME,
  FORM_NAME
}
