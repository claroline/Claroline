import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {EventPage as EventPageComponent} from '#/plugin/agenda/event/components/page'
import {actions} from '#/plugin/agenda/event/store'

const EventPage = withRouter(
  connect(
    (state) => ({
      path: toolSelectors.path(state)
    }),
    (dispatch) => ({
      delete(event) {
        return dispatch(actions.delete(event))
      }
    })
  )(EventPageComponent)
)

export {
  EventPage
}
