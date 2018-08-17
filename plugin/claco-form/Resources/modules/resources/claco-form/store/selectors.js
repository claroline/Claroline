import {createSelector} from 'reselect'

import {currentUser} from '#/main/core/user/current'
import {selectors as resourceSelect} from '#/main/core/resource/store'
import {hasPermission} from '#/main/core/resource/permissions'

const authenticatedUser = currentUser()

const STORE_NAME = 'resource'

const resource = (state) => state[STORE_NAME]

const clacoForm = createSelector(
  [resource],
  (resource) => resource.clacoForm
)

const isAnon = () => authenticatedUser === null

const params = createSelector(
  [clacoForm],
  (clacoForm) => clacoForm.details
)

const fields = createSelector(
  [clacoForm],
  (clacoForm) => clacoForm.fields
)

const visibleFields = createSelector(
  [fields],
  (fields) => fields.filter(f => !f.restrictions.hidden)
)

const template = createSelector(
  [clacoForm],
  (clacoForm) => clacoForm.template
)

const useTemplate = createSelector(
  [clacoForm],
  (clacoForm) => clacoForm.details['use_template']
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

const categories = createSelector(
  [clacoForm],
  (clacoForm) => clacoForm.categories
)

const keywords = createSelector(
  [clacoForm],
  (clacoForm) => clacoForm.keywords
)

const roles = createSelector(
  [resource],
  (resource) => resource.roles
)

const myRoles = createSelector(
  [resource],
  (resource) => resource.myRoles
)

const entryUser = createSelector(
  [entries],
  (entries) => entries.entryUser
)

const usedCountries = createSelector(
  [entries],
  (entries) => entries.countries
)

const canAdministrate = createSelector(
  resourceSelect.resourceNode,
  (resourceNode) => hasPermission('edit', resourceNode)
)

const canSearchEntry = createSelector(
  resourceSelect.resourceNode,
  isAnon,
  params,
  (resourceNode, isAnon, params) => hasPermission('edit', resourceNode) || !isAnon || params['search_enabled']
)

const isCurrentEntryOwner = createSelector(
  isAnon,
  currentEntry,
  (isAnon, currentEntry) => {
    return !isAnon && authenticatedUser && currentEntry && currentEntry.user && currentEntry.user.id === authenticatedUser.id
  }
)

const isCurrentEntryManager = createSelector(
  isAnon,
  currentEntry,
  (isAnon, currentEntry) => {
    let isManager = false

    if (!isAnon && authenticatedUser && currentEntry && currentEntry.categories) {
      currentEntry.categories.forEach(category => {
        if (!isManager && category.managers) {
          category.managers.forEach(manager => {
            if (manager.id === authenticatedUser.id) {
              isManager = true
            }
          })
        }
      })
    }

    return isManager
  }
)

const canManageCurrentEntry = createSelector(
  isAnon,
  resourceSelect.resourceNode,
  currentEntry,
  (isAnon, resourceNode, currentEntry) => {
    let canManage = hasPermission('edit', resourceNode)

    if (!canManage && !isAnon && authenticatedUser && currentEntry && currentEntry.categories) {
      currentEntry.categories.forEach(category => {
        if (!canManage && category.managers) {
          category.managers.forEach(manager => {
            if (manager.id === authenticatedUser.id) {
              canManage = true
            }
          })
        }
      })
    }

    return canManage
  }
)

const canEditCurrentEntry = createSelector(
  canAdministrate,
  params,
  isCurrentEntryOwner,
  canManageCurrentEntry,
  (canAdministrate, params, isCurrentEntryOwner, canManageCurrentEntry) => {
    return canAdministrate || (params['edition_enabled'] && isCurrentEntryOwner) || canManageCurrentEntry
  }
)

const canAddEntry = createSelector(
  canAdministrate,
  isAnon,
  params,
  myEntriesCount,
  (canAdministrate, isAnon, params, myEntriesCount) => {
    return canAdministrate || (
      params['creation_enabled'] &&
      !(isAnon && params['max_entries'] > 0) &&
      !(params['max_entries'] > 0 && myEntriesCount >= params['max_entries'])
    )
  }
)

const canOpenCurrentEntry = createSelector(
  canAdministrate,
  params,
  currentEntry,
  isCurrentEntryOwner,
  canManageCurrentEntry,
  (canAdministrate, params, currentEntry, isCurrentEntryOwner, canManageCurrentEntry) => {
    return canAdministrate || (
      currentEntry && (
        (params['search_enabled'] && currentEntry.status === 1) ||
        isCurrentEntryOwner ||
        canManageCurrentEntry
      )
    )
  }
)

const isCategoryManager = createSelector(
  categories,
  (categories) => {
    return authenticatedUser && categories.filter(c => c.managers.find(m => m.id === authenticatedUser.id)).length > 0
  }
)

const canComment = createSelector(
  params,
  myRoles,
  (params, myRoles) => {
    if (params.comments_enabled) {
      const commentsRoles = params.comments_roles || []
      const intersection = commentsRoles.filter(cr => myRoles.indexOf(cr) > -1)

      return intersection.length > 0
    }

    return false
  }
)

const canViewComments = createSelector(
  params,
  myRoles,
  (params, myRoles) => {
    if (params.display_comments) {
      const commentsDisplayRoles = params.comments_display_roles || []
      const intersection = commentsDisplayRoles.filter(cr => myRoles.indexOf(cr) > -1)

      return intersection.length > 0
    }

    return false
  }
)

const canGeneratePdf = createSelector(
  [resource],
  (resource) => resource.canGeneratePdf
)

const message = createSelector(
  [resource],
  (resource) => resource.message
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
  entries,
  isCurrentEntryOwner,
  isCurrentEntryManager,
  canManageCurrentEntry,
  canEditCurrentEntry,
  canAddEntry,
  canOpenCurrentEntry,
  canAdministrate,
  isCategoryManager,
  canComment,
  canViewComments,
  categories,
  keywords,
  roles,
  myRoles,
  entryUser,
  usedCountries,
  canGeneratePdf,
  message
}