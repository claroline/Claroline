import {selectors as baseSelectors} from '#/main/core/tools/locations/store/selectors'

const LIST_NAME = baseSelectors.STORE_NAME+'.material.list'
const FORM_NAME = baseSelectors.STORE_NAME+'.material.current'

export const selectors = {
  LIST_NAME,
  FORM_NAME
}
