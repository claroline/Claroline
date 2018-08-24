import {makeActionCreator} from '#/main/app/store/actions'

const actions = {}

const PARAMETERS_LOAD = 'PARAMETERS_LOAD'

actions.loadParameters = makeActionCreator(PARAMETERS_LOAD, 'parameters')

export {
  actions,
  PARAMETERS_LOAD
}