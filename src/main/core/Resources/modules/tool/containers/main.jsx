import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {withReducer} from '#/main/app/store/components/withReducer'
import {hasPermission} from '#/main/app/security'

import {ToolMain as ToolMainComponent} from '#/main/core/tool/components/main'
import {actions, reducer, selectors} from '#/main/core/tool/store'
import {RouteTypes, RedirectTypes} from '#/main/app/router'

const ToolMain = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      path: selectors.path(state),
      contextType: selectors.contextType(state),
      contextId: selectors.contextId(state),
      canEdit: hasPermission('edit', selectors.toolData(state))
    }),
    (dispatch) => ({
      open(toolName, context, contextId) {
        return dispatch(actions.open(toolName, context, contextId))
      }
    })
  )(ToolMainComponent)
)

ToolMain.propTypes = {
  styles: T.arrayOf(T.string),
  pages: T.arrayOf(T.shape(
    RouteTypes.propTypes
  )),
  redirect: T.arrayOf(T.shape(
    RedirectTypes.propTypes
  )),
  children: T.node
}

export {
  ToolMain
}
