import {createSelector} from 'reselect'

import {currentUser} from '#/main/core/user/current'
import {selectors as resourceSelect} from '#/main/core/resource/store'
import {hasPermission} from '#/main/core/resource/permissions'

const authenticatedUser = currentUser()

const clacoForm = state => state.clacoForm
const isAnon = () => authenticatedUser === null
const params = state => state.clacoForm.details
const fields = state => state.clacoForm.fields
const visibleFields = state => state.clacoForm.fields.filter(f => !f.restrictions.hidden)
const template = state => state.clacoForm.template
const useTemplate = state => state.clacoForm.details['use_template']
const getParam = (state, property) => state.clacoForm.details[property]
const currentEntry = state => state.entries.current.data
const myEntriesCount = state => state.entries.myEntriesCount
const categories = state => state.clacoForm.categories
const keywords = state => state.clacoForm.keywords
const myRoles = state => state.myRoles
const entryUser = state => state.entries.entryUser
const usedCountries = state => state.entries.countries

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

export const select = {
  clacoForm,
  isAnon,
  canSearchEntry,
  fields,
  visibleFields,
  template,
  useTemplate,
  getParam,
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
  entryUser,
  usedCountries
}