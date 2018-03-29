import {makeActionCreator} from '#/main/core/scaffolding/actions'

export const STEP_ADD = 'STEP_ADD'
export const STEP_REMOVE = 'STEP_REMOVE'
export const STEP_UPDATE_PRIMARY_RESOURCE = 'STEP_UPDATE_PRIMARY_RESOURCE'
export const STEP_PASTE = 'STEP_PASTE'

export const STEP_ADD_SECONDARY_RESOURCES = 'STEP_ADD_SECONDARY_RESOURCES'
export const STEP_REMOVE_SECONDARY_RESOURCES = 'STEP_REMOVE_SECONDARY_RESOURCES'

export const STEP_UPDATE_SECONDARY_RESOURCE_INHERITANCE = 'STEP_UPDATE_SECONDARY_RESOURCE_INHERITANCE'

export const STEP_REMOVE_INHERITED_RESOURCES = 'STEP_REMOVE_INHERITED_RESOURCES'

export const STEP_COPY = 'STEP_COPY'
export const STEP_COPY_RESET = 'STEP_COPY_RESET'

export const actions = {}

actions.addStep = makeActionCreator(STEP_ADD, 'parentId')
actions.removeStep = makeActionCreator(STEP_REMOVE, 'id')
actions.updatePrimaryResource = makeActionCreator(STEP_UPDATE_PRIMARY_RESOURCE, 'stepId', 'resource')
actions.pasteStep = makeActionCreator(STEP_PASTE, 'parentId', 'step')

actions.paste = (parentId) => (dispatch, getState) => {
  // retrieves the copy
  const state = getState()

  dispatch(actions.pasteStep(parentId, state.pathForm.copy))
  dispatch(actions.resetStepCopy())
}

actions.addSecondaryResources = makeActionCreator(STEP_ADD_SECONDARY_RESOURCES, 'stepId', 'resources')
actions.removeSecondaryResources = makeActionCreator(STEP_REMOVE_SECONDARY_RESOURCES, 'stepId', 'resources')
actions.updateSecondaryResourceInheritance = makeActionCreator(STEP_UPDATE_SECONDARY_RESOURCE_INHERITANCE, 'stepId', 'id', 'value')

actions.removeInheritedResources = makeActionCreator(STEP_REMOVE_INHERITED_RESOURCES, 'stepId', 'resources')

actions.copyStep = makeActionCreator(STEP_COPY, 'step')
actions.resetStepCopy = makeActionCreator(STEP_COPY_RESET)