import {connect} from 'react-redux'
import {makeId} from '#/main/core/scaffolding/id'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {actions} from '#/main/core/administration/connection-messages/store/actions'
import {selectors} from '#/main/core/administration/connection-messages/store/selectors'

import {ConnectionMessage as ConnectionMessageTypes} from '#/main/core/data/types/connection-message/prop-types'
import {ConnectionMessagesTool as ConnectionMessagesToolComponent} from '#/main/core/administration/connection-messages/components/tool'

const ConnectionMessagesTool = connect(
  (state) => ({
    path: toolSelectors.path(state)
  }),
  (dispatch) => ({
    openConnectionMessageForm(id) {
      const defaultProps = Object.assign({}, ConnectionMessageTypes.defaultProps, {
        id: makeId()
      })
      dispatch(actions.openConnectionMessageForm(selectors.STORE_NAME+'.current', defaultProps, id))
    },
    resetConnectionMessageForm() {
      dispatch(actions.resetConnectionMessageForm(selectors.STORE_NAME+'.current'))
    }
  })
)(ConnectionMessagesToolComponent)

export {
  ConnectionMessagesTool
}
