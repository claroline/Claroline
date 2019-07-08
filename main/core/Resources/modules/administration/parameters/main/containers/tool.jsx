import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {makeId} from '#/main/core/scaffolding/id'
import {ConnectionMessage as ConnectionMessageType} from '#/main/core/administration/parameters/main/prop-types'
import {actions} from '#/main/core/administration/parameters/main/store'
import {ParametersTool as ParametersToolComponent} from '#/main/core/administration/parameters/main/components/tool'

const ParametersTool = withRouter(connect(
  null,
  (dispatch) => ({
    openConnectionMessageForm(id = null) {
      const defaultProps = Object.assign({}, ConnectionMessageType.defaultProps, {
        id: makeId()
      })
      dispatch(actions.openConnectionMessageForm('messages.current', defaultProps, id))
    },
    resetConnectionMessageFrom() {
      dispatch(actions.resetForm('messages.current'))
    }
  })
)(ParametersToolComponent))

export {
  ParametersTool
}
