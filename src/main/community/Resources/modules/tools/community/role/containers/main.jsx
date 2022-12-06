import {connect} from 'react-redux'
import isEmpty from 'lodash/isEmpty'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {constants} from '#/main/community/constants'
import {RoleMain as RoleMainComponent} from '#/main/community/tools/community/role/components/main'
import {actions, selectors} from '#/main/community/tools/community/role/store'

const RoleMain = connect(
  state => ({
    path: toolSelectors.path(state),
    contextData: toolSelectors.contextData(state),
    canCreate: selectors.canCreate(state)
  }),
  dispatch => ({
    open(id, contextData) {
      dispatch(actions.open(id, contextData))
    },
    new(contextData) {
      dispatch(actions.new(!isEmpty(contextData) ? {
        type: constants.ROLE_WORKSPACE,
        workspace: contextData
      } : {
        type: constants.ROLE_PLATFORM
      }))
    }
  })
)(RoleMainComponent)

export {
  RoleMain
}
