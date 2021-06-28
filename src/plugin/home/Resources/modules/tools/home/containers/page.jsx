import {connect} from 'react-redux'

import {hasPermission} from '#/main/app/security'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {HomePage as HomePageComponent} from '#/plugin/home/tools/home/components/page'
import {actions, selectors} from '#/plugin/home/tools/home/store'
import {selectors as configSelectors} from '#/main/app/config/store'

const HomePage = connect(
  (state) => ({
    basePath: toolSelectors.path(state),
    currentContext: toolSelectors.context(state),
    canEdit: hasPermission('edit', toolSelectors.toolData(state)),
    canAdministrate: hasPermission('administrate', toolSelectors.toolData(state)),

    administration: selectors.administration(state),
    showSubMenu: configSelectors.param(state, 'home.show_sub_menu')
  }),
  (dispatch) => ({
    setAdministration(administration) {
      dispatch(actions.setAdministration(administration))
    },
    fetchTabs(context, administration) {
      dispatch(actions.fetchTabs(context, administration))
    }
  })
)(HomePageComponent)

export {
  HomePage
}
