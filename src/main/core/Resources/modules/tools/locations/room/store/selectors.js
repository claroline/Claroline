import {selectors as baseSelectors} from '#/main/core/tools/locations/store/selectors'

const LIST_NAME = baseSelectors.STORE_NAME+'.room.list'
const FORM_NAME = baseSelectors.STORE_NAME+'.room.current'

export const selectors = {
  LIST_NAME,
  FORM_NAME
}
