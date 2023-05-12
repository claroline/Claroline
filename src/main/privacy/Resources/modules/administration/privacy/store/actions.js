import {makeActionCreator} from '#/main/app/store/actions'
export const SET_PLATFORM_OPTIONS = 'SET_PLATFORM_OPTIONS'
export const ACTION_UPDATE_FORM = 'ACTION_UPDATE_FORM'

export const actions = {setPlatformOptions(options) {
  return { type: SET_PLATFORM_OPTIONS, options }
}}

const action = makeActionCreator(ACTION_UPDATE_FORM, 'data')
actions.updateForm = (data) => (dispatch) => dispatch(action(data))
