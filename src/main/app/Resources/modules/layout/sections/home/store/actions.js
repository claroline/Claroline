
import {selectors as configSelectors} from '#/main/app/config/store/selectors'
import {actions as menuActions} from '#/main/app/layout/menu/store/actions'

export const actions = {}

actions.open = () => (dispatch, getState) => {
  // set menu state based on home configuration
  dispatch(menuActions.setState(configSelectors.param(getState(), 'home.menu', null)))
}
