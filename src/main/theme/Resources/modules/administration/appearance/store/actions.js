import {makeActionCreator} from '#/main/app/store/actions'

export const APPEARANCE_ADD_ICON_SET = 'APPEARANCE_ADD_ICON_SET'
export const APPEARANCE_REMOVE_ICON_SET = 'APPEARANCE_REMOVE_ICON_SET'

export const APPEARANCE_ADD_COLOR_CHART = 'APPEARANCE_ADD_COLOR_CHART'
export const APPEARANCE_REMOVE_COLOR_CHART = 'APPEARANCE_REMOVE_COLOR_CHART'

export const actions = {}

actions.addIconSet = makeActionCreator(APPEARANCE_ADD_ICON_SET, 'iconSet')
actions.removeIconSet = makeActionCreator(APPEARANCE_REMOVE_ICON_SET, 'iconSet')

actions.addColorChart = makeActionCreator(APPEARANCE_ADD_COLOR_CHART, 'colorChart')
actions.removeColorChart = makeActionCreator(APPEARANCE_REMOVE_COLOR_CHART, 'colorChart')
