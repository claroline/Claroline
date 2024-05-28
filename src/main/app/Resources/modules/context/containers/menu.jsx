import {connect} from 'react-redux'
import isEmpty from 'lodash/isEmpty'

import {withRouter} from '#/main/app/router'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {ContextMenu as ContextMenuComponent} from '#/main/app/context/components/menu'
import {selectors, actions} from '#/main/app/context/store'

const ContextMenu = withRouter(
  connect(
    (state) => ({
      currentUser: securitySelectors.currentUser(state),
      path: selectors.path(state),
      contextType: selectors.type(state),
      contextData: selectors.data(state),
      opened: selectors.menuOpened(state),
      untouched: selectors.menuUntouched(state),
      notFound: selectors.notFound(state),
      hasErrors: !isEmpty(selectors.accessErrors(state)),
      //tools: contextSelectors.tools(state)
    }),
    (dispatch) => ({
      reload() {
        dispatch(actions.reload())
      },
      close() {
        dispatch(actions.closeMenu())
      }
    })
  )(ContextMenuComponent)
)

export {
  ContextMenu
}
