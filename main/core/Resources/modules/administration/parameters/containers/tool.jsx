import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {makeId} from '#/main/core/scaffolding/id'
import {ConnectionMessage as ConnectionMessageType} from '#/main/core/administration/parameters/prop-types'
import {actions, selectors} from '#/main/core/administration/parameters/store'
import {ParametersTool as ParametersToolComponent} from '#/main/core/administration/parameters/components/tool'

const ParametersTool = withRouter(connect(
  null,
  (dispatch) => ({
    openConnectionMessageForm(id = null) {
      const defaultProps = Object.assign({}, ConnectionMessageType.defaultProps, {
        id: makeId()
      })
      dispatch(actions.openConnectionMessageForm(selectors.STORE_NAME+'.messages.current', defaultProps, id))
    },
    resetConnectionMessageFrom() {
      dispatch(actions.resetForm(selectors.STORE_NAME+'.messages.current'))
    }
  })
)(ParametersToolComponent))

export {
  ParametersTool
}
