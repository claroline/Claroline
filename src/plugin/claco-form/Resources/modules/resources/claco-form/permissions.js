function isEntryManager(entry, currentUser) {
  let isManager = false

  if (entry.categories && currentUser) {
    entry.categories.forEach(c => {
      if (!isManager && c.managers) {
        c.managers.forEach(m => {
          if (m.id === currentUser.id) {
            isManager = true
          }
        })
      }
    })
  }

  return isManager
}

function isEntryOwner(entry, currentUser) {
  return currentUser && entry.user && entry.user.id === currentUser.id
}

function canEditEntry(entry, clacoForm, currentUser) {
  return canManageEntry(entry, false, currentUser) || (clacoForm.details.edition_enabled && isEntryOwner(entry, currentUser))
}

function canManageEntry(entry, canEdit = false, currentUser = null) {
  return canEdit || isEntryManager(entry, currentUser)
}

function canViewEntryMetadata(entry, clacoForm, canEdit = false, currentUser = null) {
  return canEdit
    || 'all' === clacoForm.details.display_metadata
    || isEntryOwner(entry, currentUser)
    || ('manager' === clacoForm.details.display_metadata && isEntryManager(entry, currentUser))
}

export {
  isEntryManager,
  isEntryOwner,
  canEditEntry,
  canManageEntry,
  canViewEntryMetadata
}
