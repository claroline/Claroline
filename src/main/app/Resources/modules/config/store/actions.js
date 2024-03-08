import {makeActionCreator} from '#/main/app/store/actions'

export const APP_CONFIG_LOAD = 'APP_CONFIG_LOAD'
export const APP_CONFIG_UPDATE = 'APP_CONFIG_UPDATE'

export const actions = {}

actions.loadConfig = makeActionCreator(APP_CONFIG_LOAD, 'config')
actions.updateConfig = makeActionCreator(APP_CONFIG_UPDATE, 'configKey', 'configValue')
