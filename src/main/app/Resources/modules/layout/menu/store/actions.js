import {makeActionCreator} from '#/main/app/store/actions'

// actions
export const MENU_OPEN = 'MENU_OPEN'
export const MENU_CLOSE = 'MENU_CLOSE'
export const MENU_TOGGLE = 'MENU_TOGGLE'
export const MENU_CHANGE_SECTION = 'MENU_CHANGE_SECTION'

// action creators
export const actions = {}

actions.open = makeActionCreator(MENU_OPEN)
actions.close = makeActionCreator(MENU_CLOSE)
actions.toggle = makeActionCreator(MENU_TOGGLE)
actions.changeSection = makeActionCreator(MENU_CHANGE_SECTION, 'section')


actions.setState = (state = null) => (dispatch) => {
  switch (state) {
    case 'open':
      // force open the menu
      dispatch(actions.open())
      break

    case 'close':
      // force close the menu
      dispatch(actions.close())
      break

    default:
      // let the menu in its previous state
  }
}
