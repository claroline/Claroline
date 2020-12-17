import {connect} from 'react-redux'

import {hasPermission} from '#/main/app/security'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {HomePage as HomePageComponent} from '#/plugin/home/tools/home/components/page'
import {actions, selectors} from '#/plugin/home/tools/home/store'

const HomePage = connect(
  (state) => ({
    basePath: toolSelectors.path(state),
    currentContext: toolSelectors.context(state),
    canEdit: hasPermission('edit', toolSelectors.toolData(state)),
    canAdministrate: hasPermission('administrate', toolSelectors.toolData(state)),

    administration: selectors.administration(state)
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
