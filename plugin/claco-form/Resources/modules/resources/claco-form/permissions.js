import {currentUser} from '#/main/app/security'

function isEntryManager(entry) {
  const authenticatedUser = currentUser()

  let isManager = false

  if (entry.categories && authenticatedUser) {
    entry.categories.forEach(c => {
      if (!isManager && c.managers) {
        c.managers.forEach(m => {
          if (m.id === authenticatedUser.id) {
            isManager = true
          }
        })
      }
    })
  }

  return isManager
}

function isEntryOwner(entry) {
  const authenticatedUser = currentUser()

  return authenticatedUser && entry.user && entry.user.id === authenticatedUser.id
}

function canEditEntry(entry, clacoForm) {
  return canManageEntry(entry) || (clacoForm.details.editionEnabled && isEntryOwner(entry))
}

function canManageEntry(entry, canEdit = false) {
  return canEdit || isEntryManager(entry)
}

function canViewEntryMetadata(entry, clacoForm, canEdit = false) {
  return canEdit
    || 'all' === clacoForm.details.display_metadata
    || isEntryOwner(entry)
    || ('manager' === clacoForm.details.display_metadata && isEntryManager(entry))
}

export {
  isEntryManager,
  isEntryOwner,
  canEditEntry,
  canManageEntry,
  canViewEntryMetadata
}
