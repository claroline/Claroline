import {param} from '#/main/app/config'

import {selectors as resourceSelect} from '#/main/core/resource/store'
import {hasPermission} from '#/main/core/resource/permissions'

import {findInTree} from '#/plugin/wiki/resources/wiki/utils'

const wiki = (state) => state.wiki

const section = (state, id) => findInTree(state.sections.tree, id, 'children', 'id')

const canEdit = (state) => hasPermission('edit', resourceSelect.resourceNode(state))
const canExport = (state) => hasPermission('export', resourceSelect.resourceNode(state)) && param('is_pdf_export_active')

export const selectors = {
  wiki,
  section,
  canEdit,
  canExport
}
