
import {selectors as listSelectors} from '#/main/app/content/list/store/selectors'

import {selectors as baseSelectors} from '#/main/community/administration/community/store'
import {flattenTree} from '#/main/app/content/tree/utils'

const FORM_NAME = baseSelectors.STORE_NAME+'.organizations.current'
const LIST_NAME = baseSelectors.STORE_NAME+'.organizations.list'

const organizations = state => state.organizations

const flattenedOrganizations = (state) => flattenTree(
  listSelectors.data(listSelectors.list(state, LIST_NAME))
)

export const selectors = {
  FORM_NAME,
  LIST_NAME,

  organizations,
  flattenedOrganizations
}
