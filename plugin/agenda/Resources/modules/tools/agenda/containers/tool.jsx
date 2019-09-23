import {connect} from 'react-redux'
import isEmpty from 'lodash/isEmpty'
import merge from 'lodash/merge'

import {withRouter} from '#/main/app/router'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'
import {actions as modalActions} from '#/main/app/overlays/modal/store'

import {MODAL_EVENT_PARAMETERS} from '#/plugin/agenda/event/modals/parameters'
import {AgendaTool as AgendaToolComponent} from '#/plugin/agenda/tools/agenda/components/tool'
import {actions, selectors} from '#/plugin/agenda/tools/agenda/store'

const AgendaTool = withRouter(
  connect(
    (state) => ({
      contextData: toolSelectors.contextData(state),
      currentUser: securitySelectors.currentUser(state),

      view: selectors.view(state),
      referenceDate: selectors.referenceDate(state),

      loaded: selectors.loaded(state),
      events: selectors.events(state)
    }),
    (dispatch) => ({
      changeView(view, referenceDate) {
        dispatch(actions.changeView(view, referenceDate))
      },
      load(rangeDates) {
        dispatch(actions.fetch(rangeDates))
      },
      create(event, context, user) {
        dispatch(modalActions.showModal(MODAL_EVENT_PARAMETERS, {
          event: merge({}, event, {
            workspace: !isEmpty(context) ? context : null,
            meta: {
              creator: user
            }
          }),
          // TODO : only reload if event is created in the current range
          onSave: () => dispatch(actions.setLoaded(false))
        }))
      },
      update(/*event*/) {
        dispatch(actions.setLoaded(false))
      },
      delete(event) {
        dispatch(actions.delete(event))
      },
      markDone(event) {
        dispatch(actions.markDone(event))
      },
      markTodo(event) {
        dispatch(actions.markTodo(event))
      },
      import(data, workspace = null) {
        dispatch(actions.import(data, workspace))
      }
    })
  )(AgendaToolComponent)
)

export {
  AgendaTool
}
