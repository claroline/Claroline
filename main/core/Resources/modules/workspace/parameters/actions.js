import {makeActionCreator} from '#/main/core/scaffolding/actions'

export const UPDATE_RESOURCE = 'UPDATE_RESOURCE'

export const actions = {}

actions.updateResource = makeActionCreator(UPDATE_RESOURCE, 'resource')
