import {createSelector} from 'reselect'

import {flattenTree} from '#/main/app/content/tree/utils'

const organizations = state => state.organizations

const flattenedOrganizations = createSelector(
  [organizations],
  (organizations) => flattenTree(organizations.list.data)
)

export const select = {
  organizations,
  flattenedOrganizations
}
