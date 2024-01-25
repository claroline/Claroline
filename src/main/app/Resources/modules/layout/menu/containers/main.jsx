import {connect} from 'react-redux'

import {MenuMain as MenuMainComponent} from '#/main/app/layout/menu/components/main'
import {actions, selectors} from '#/main/app/layout/menu/store'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as configSelectors} from '#/main/app/config/store'

import {selectors as headerSelectors} from '#/main/app/layout/header/store'

const MenuMain =
  connect(
    (state) => ({
      opened: selectors.opened(state),
      untouched: selectors.untouched(state),
      section: selectors.openedSection(state),
      currentUser: securitySelectors.currentUser(state),

      showTitle: headerSelectors.display(state).name,

      // platform parameters
      logo: configSelectors.param(state, 'logo'),
      title: configSelectors.param(state, 'name'),
      subtitle: configSelectors.param(state, 'secondaryName'),
    }),
    (dispatch) => ({
      close() {
        dispatch(actions.close())
      },
      changeSection(section) {
        dispatch(actions.changeSection(section))
      }
    })
  )(MenuMainComponent)

export {
  MenuMain
}