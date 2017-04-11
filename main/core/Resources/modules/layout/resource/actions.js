import {makeActionCreator} from '#/main/core/utilities/redux'

export const RESOURCE_PUBLICATION_TOGGLE = 'RESOURCE_PUBLICATION_CHANGE'
export const RESOURCE_OPEN_PROPERTIES    = 'RESOURCE_OPEN_PROPERTIES'
export const RESOURCE_EDIT_PROPERTIES    = 'RESOURCE_EDIT_PROPERTIES'
export const RESOURCE_OPEN_RIGHTS        = 'RESOURCE_OPEN_RIGHTS'
export const RESOURCE_EDIT_RIGHTS        = 'RESOURCE_EDIT_RIGHTS'

export const actions = {}

actions.togglePublication = makeActionCreator(RESOURCE_PUBLICATION_TOGGLE)

// Properties management
actions.openProperties    = makeActionCreator(RESOURCE_OPEN_PROPERTIES)
actions.editProperties    = makeActionCreator(RESOURCE_EDIT_PROPERTIES)

// Rights management
actions.openRights        = makeActionCreator(RESOURCE_OPEN_RIGHTS)
actions.editRights        = makeActionCreator(RESOURCE_EDIT_RIGHTS)
