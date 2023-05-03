import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {makeId} from '#/main/core/scaffolding/id'
import {ConnectionMessage as ConnectionMessageTypes} from '#/main/core/data/types/connection-message/prop-types'
import {ConnectionMessagesTool as ConnectionMessagesToolComponent} from '#/main/core/administration/connection-messages/components/tool'

const ConnectionMessagesTool = withRouter(connect(
    null,
    (dispatch) => ({
        openConnectionMessageForm(id = null) {
            const defaultProps = Object.assign({}, ConnectionMessageTypes.defaultProps, {
                id: makeId()
            })
            dispatch(actions.openConnectionMessageForm(selectors.STORE_NAME+'.messages.current', defaultProps, id))
        },
        resetConnectionMessageFrom() {
            dispatch(actions.resetForm(selectors.STORE_NAME+'.messages.current'))
        }
    })
)(ConnectionMessagesToolComponent))

export {
    ConnectionMessagesTool
}
