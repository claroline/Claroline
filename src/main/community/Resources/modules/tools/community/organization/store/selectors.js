import {createSelector} from 'reselect'

import {hasPermission} from '#/main/app/security'
import {selectors as listSelectors} from '#/main/app/content/list/store/selectors'
import {flattenTree} from '#/main/app/content/tree/utils'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {selectors as baseSelectors} from '#/main/community/tools/community/store/selectors'

const STORE_NAME = baseSelectors.STORE_NAME+'.organizations'
const FORM_NAME = STORE_NAME+'.current'
const LIST_NAME = STORE_NAME+'.list'

const organizationsList = (state) => listSelectors.data(listSelectors.list(state, LIST_NAME))

const flattenedOrganizations = createSelector(
  [organizationsList],
  (organizations) => flattenTree(organizations)
)

const canCreate = createSelector(
  [toolSelectors.toolData],
  (tool) => hasPermission('edit', tool)
)

export const selectors = {
  STORE_NAME,
  FORM_NAME,
  LIST_NAME,

  flattenedOrganizations,
  canCreate
}
