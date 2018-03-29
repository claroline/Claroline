import {makeActionCreator} from '#/main/core/scaffolding/actions'

export const USER_EVALUATION_UPDATE = 'USER_EVALUATION_UPDATE'

export const actions = {}

actions.updateUserEvaluation = makeActionCreator(USER_EVALUATION_UPDATE, 'userEvaluation')
