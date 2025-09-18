import {createSelector} from 'reselect'

import {hasPermission} from '#/main/app/security'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {selectors as formSelectors} from '#/main/app/content/form/store'

const STORE_NAME = 'organizations'
const FORM_NAME = STORE_NAME+'.current'
const LIST_NAME = STORE_NAME+'.list'

const currentId = (state) => formSelectors.data(formSelectors.form(state, FORM_NAME)).id || null

const canCreate = createSelector(
  [toolSelectors.tool],
  (tool) => hasPermission('edit', tool)
)

export const selectors = {
  STORE_NAME,
  FORM_NAME,
  LIST_NAME,

  currentId,
  canCreate
}
