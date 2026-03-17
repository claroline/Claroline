import {createSelector} from 'reselect'
import get from 'lodash/get'

import {hasPermission} from '#/main/app/security'
import {selectors as securitySelectors} from '#/main/app/security/store'

import {selectors as resourceSelect} from '#/main/core/resource/store'

const STORE_NAME = 'claroline_claco_form'

const resource = (state) => state[STORE_NAME]
const authenticatedUser = (state) => securitySelectors.currentUser(state)
const isAnon = (state) => !securitySelectors.isAuthenticated(state)

const clacoForm = createSelector(
  [resource],
  (resource) => resource.resource
)

const hasStatistics = createSelector(
  [clacoForm],
  clacoForm => get(clacoForm, 'display.statistics')
)

const params = createSelector(
  [clacoForm],
  (clacoForm) => clacoForm.details || {}
)

const fields = createSelector(
  [clacoForm],
  (clacoForm) => clacoForm.fields
)

const visibleFields = createSelector(
  [fields],
  (fields) => []
    .concat(fields || [])
    .sort((a, b) => {
      if (get(a, 'display.order') < get(b, 'display.order')) {
        return -1
      }

      if (get(a, 'display.order') > get(b, 'display.order')) {
        return 1
      }

      return 0
    })
    .filter(f => !f.restrictions.hidden)
)

const template = createSelector(
  [clacoForm],
  (clacoForm) => get(clacoForm, 'template.content')
)

const useTemplate = createSelector(
  [clacoForm],
  (clacoForm) => get(clacoForm, 'template.enabled')
)

const showConfirm = createSelector(
  [clacoForm],
  (clacoForm) => get(clacoForm, 'display.showConfirm', false)
)

const confirmMessage = createSelector(
  [clacoForm],
  (clacoForm) => get(clacoForm, 'display.confirmMessage', null)
)

const entries = createSelector(
  [resource],
  (resource) => resource.entries
)

const currentEntry = createSelector(
  [entries],
  (entries) => entries.current.data
)

const myEntriesCount = createSelector(
  [entries],
  (entries) => entries.myEntriesCount
)

const listConfiguration = createSelector(
  [clacoForm],
  (clacoForm) => clacoForm.list
)

const categories = createSelector(
  [resource],
  (resource) => resource.categories
)

const usedCountries = createSelector(
  [entries],
  (entries) => entries.countries
)

const canEdit = createSelector(
  resourceSelect.resourceNode,
  (resourceNode) => hasPermission('edit', resourceNode)
)

const canAdministrate = createSelector(
  resourceSelect.resourceNode,
  (resourceNode) => hasPermission('administrate', resourceNode)
)

const canSearchEntry = createSelector(
  [resourceSelect.resourceNode, isAnon, params],
  (resourceNode, isAnon, params) => hasPermission('edit', resourceNode) || !isAnon || (params && params['search_enabled'])
)

const isCurrentEntryOwner = createSelector(
  [authenticatedUser, isAnon, currentEntry],
  (authenticatedUser, isAnon, currentEntry) => {
    return !isAnon && authenticatedUser && currentEntry && currentEntry.user && currentEntry.user.id === authenticatedUser.id
  }
)

const canManageCurrentEntry = createSelector(
  currentEntry,
  (currentEntry) => {
    return currentEntry && hasPermission('administrate', currentEntry)
  }
)

const canEditCurrentEntry = createSelector(
  currentEntry,
  (currentEntry) => {
    return currentEntry && hasPermission('edit', currentEntry)
  }
)

const canAddEntry = createSelector(
  resourceSelect.resourceNode,
  params,
  myEntriesCount,
  (resourceNode, params, myEntriesCount) => {
    return hasPermission('edit', resourceNode)
      || (hasPermission('contribute', resourceNode) && (!params.max_entries || myEntriesCount < params.max_entries))
  }
)

const canOpenCurrentEntry = createSelector(
  currentEntry,
  (currentEntry) => {
    return currentEntry && hasPermission('open', currentEntry)
  }
)

const isCategoryManager = createSelector(
  [authenticatedUser, categories],
  (authenticatedUser, categories) => {
    return authenticatedUser && categories.filter(c => c.managers.find(m => m.id === authenticatedUser.id)).length > 0
  }
)

const canViewMetadata = createSelector(
  [canEdit, params, isCategoryManager],
  (canEdit, params, isCategoryManager) => canEdit
  || 'all' === params.display_metadata
  || ('manager' === params.display_metadata && isCategoryManager)
)


const canDownload = createSelector(
  [resourceSelect.downloadable],
  (downloadable) => downloadable
)

const message = createSelector(
  [resource],
  (resource) => resource.message
)

const showEntryNav = createSelector(
  [clacoForm],
  (clacoForm) => get(clacoForm, 'display.showEntryNav')
)

export const selectors = {
  STORE_NAME,

  resource,
  clacoForm,
  isAnon,
  params,
  canSearchEntry,
  fields,
  visibleFields,
  template,
  useTemplate,
  showConfirm,
  confirmMessage,
  entries,
  isCurrentEntryOwner,
  canManageCurrentEntry,
  canEditCurrentEntry,
  canViewMetadata,
  canAddEntry,
  canEdit,
  canOpenCurrentEntry,
  canAdministrate,
  isCategoryManager,
  categories,
  usedCountries,
  canDownload,
  message,
  listConfiguration,
  showEntryNav,
  hasStatistics
}
