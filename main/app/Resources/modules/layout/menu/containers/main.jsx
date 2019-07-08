import {connect} from 'react-redux'

import {MenuMain as MenuMainComponent} from '#/main/app/layout/menu/components/main'
import {actions, selectors} from '#/main/app/layout/menu/store'

const MenuMain =
  connect(
    (state) => ({
      section: selectors.openedSection(state)
    }),
    (dispatch) => ({
      changeSection(section) {
        dispatch(actions.changeSection(section))
      }
    })
  )(MenuMainComponent)

export {
  MenuMain
}