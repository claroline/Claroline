import {connect} from 'react-redux'
import {actions, selectors} from '#/main/core/administration/connection-messages/store'
import {ConnectionMessagesTool as ConnectionMessagesToolComponent} from '#/main/core/administration/connection-messages/components/tool'
import {ConnectionMessage as ConnectionMessageTypes} from "#/main/core/data/types/connection-message/prop-types";
import {makeId} from "#/main/core/scaffolding/id";

const ConnectionMessagesTool = connect(
  null,
  (dispatch) => ({
    openConnectionMessageForm(id) {
      const defaultProps = Object.assign({}, ConnectionMessageTypes.defaultProps, {
        id: makeId()
      })
      dispatch(actions.openConnectionMessageForm(selectors.STORE_NAME+'.current', defaultProps, id))
    },
    resetConnectionMessageForm() {
      dispatch(actions.resetConnectionMessageForm())
    }
  })
)(ConnectionMessagesToolComponent)

export {
  ConnectionMessagesTool
}
