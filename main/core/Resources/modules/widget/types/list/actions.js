import {makeActionCreator} from '#/main/core/scaffolding/actions'

export const WIDGET_UPDATE_CONFIG = 'WIDGET_UPDATE_CONFIG'

export const actions = {}

actions.updateWidgetConfig = makeActionCreator(WIDGET_UPDATE_CONFIG, 'config')
