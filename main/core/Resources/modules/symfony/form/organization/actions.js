export const CHANGE_ORGANIZATION = 'CHANGE_ORGANIZATION'

import {makeActionCreator} from '#/main/core/scaffolding/actions'

export const actions = {}

actions.onChange = makeActionCreator(CHANGE_ORGANIZATION, 'organization')
