import {selectors as baseSelectors}  from '#/plugin/open-badge/tools/badges/store/selectors'

const FORM_NAME = `${baseSelectors.STORE_NAME}.badges.current`
const LIST_NAME = `${baseSelectors.STORE_NAME}.badges.list`

export const selectors = {
  FORM_NAME,
  LIST_NAME
}
