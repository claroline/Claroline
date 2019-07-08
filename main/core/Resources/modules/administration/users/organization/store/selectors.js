
import {selectors as listSelectors} from '#/main/app/content/list/store/selectors'

import {selectors as baseSelectors} from '#/main/core/administration/users/store'
import {flattenTree} from '#/main/app/content/tree/utils'

const organizations = state => state.organizations

const flattenedOrganizations = (state) => flattenTree(
  listSelectors.data(listSelectors.list(state, baseSelectors.STORE_NAME+'.organizations.list'))
)

export const selectors = {
  organizations,
  flattenedOrganizations
}
