import {makeActionCreator} from '#/main/core/utilities/redux'

export const OBJECT_SELECT = 'OBJECT_SELECT'

export const actions = {}

actions.selectObject = makeActionCreator(OBJECT_SELECT, 'type', 'id')
