// I declare them here because this is internal modals
// and I don't want them to be exposed in the public file of the creation modal.
// If I declare them in their modal component file I get a circular reference
const MODAL_RESOURCE_CREATION_INTERNAL_PARAMETERS = 'MODAL_RESOURCE_CREATION_INTERNAL_PARAMETERS'
const MODAL_RESOURCE_CREATION_INTERNAL_RIGHTS = 'MODAL_RESOURCE_CREATION_INTERNAL_RIGHTS'

export const constants = {
  MODAL_RESOURCE_CREATION_INTERNAL_PARAMETERS,
  MODAL_RESOURCE_CREATION_INTERNAL_RIGHTS
}
