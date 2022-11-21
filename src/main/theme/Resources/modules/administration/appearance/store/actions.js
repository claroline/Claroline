import {makeActionCreator} from '#/main/app/store/actions'

export const APPEARANCE_ADD_ICON_SET = 'APPEARANCE_ADD_ICON_SET'
export const APPEARANCE_REMOVE_ICON_SET = 'APPEARANCE_REMOVE_ICON_SET'

export const actions = {}

actions.addIconSet = makeActionCreator(APPEARANCE_ADD_ICON_SET, 'iconSet')
actions.removeIconSet = makeActionCreator(APPEARANCE_REMOVE_ICON_SET, 'iconSet')
