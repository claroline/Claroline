import {connect} from 'react-redux'

import {MenuMain as MenuMainComponent} from '#/main/app/layout/menu/components/main'
import {actions, selectors} from '#/main/app/layout/menu/store'
/*import {selectors as configSelectors} from '#/main/app/config/store'

import {selectors as headerSelectors} from '#/main/app/layout/header/store'*/

const MenuMain =
  connect(
    (state) => ({
      untouched: selectors.untouched(state),
      section: selectors.openedSection(state),

      /*display: headerSelectors.display(state),
      // platform parameters
      logo: configSelectors.param(state, 'logo'),
      title: configSelectors.param(state, 'name'),
      subtitle: configSelectors.param(state, 'secondaryName'),
      helpUrl: configSelectors.param(state, 'helpUrl'),*/
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