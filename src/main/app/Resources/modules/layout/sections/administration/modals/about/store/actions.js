import {API_REQUEST} from '#/main/app/api'
import {makeActionCreator} from '#/main/app/store/actions'

export const LOAD_PLATFORM_ABOUT = 'LOAD_PLATFORM_ABOUT'

export const actions = {}

actions.load = makeActionCreator(LOAD_PLATFORM_ABOUT, 'version', 'changelogs')

actions.get = () => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['apiv2_platform_version'],
    silent: true,
    success: (data) => dispatch(actions.load(data.version, data.changelogs))
  }
})
