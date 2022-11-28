import {createSelector} from 'reselect'

import {hasPermission} from '#/main/app/security'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {selectors as baseSelectors} from '#/main/community/tools/community/store/selectors'

const STORE_NAME = baseSelectors.STORE_NAME + '.roles'

const LIST_NAME = STORE_NAME + '.list'
const FORM_NAME = STORE_NAME + '.current'

const canCreate = createSelector(
  [toolSelectors.toolData],
  (tool) => hasPermission('edit', tool)
)

export const selectors = {
  STORE_NAME,
  LIST_NAME,
  FORM_NAME,

  canCreate
}
