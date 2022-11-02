import {selectors as baseSelectors} from '#/main/community/tools/community/store/selectors'

const LIST_NAME = baseSelectors.STORE_NAME + '.roles.list'
const FORM_NAME = baseSelectors.STORE_NAME + '.roles.current'

export const selectors = {
  LIST_NAME,
  FORM_NAME
}
