import {PropTypes as T} from 'prop-types'

import {Event as EventTypes} from '#/plugin/agenda/event/prop-types'

const AgendaView = {
  propTypes: {
    path: T.string.isRequired,
    loaded: T.bool.isRequired,
    view: T.string.isRequired,
    referenceDate: T.object,
    range: T.arrayOf(T.object),
    previous: T.func.isRequired,
    next: T.func.isRequired,

    events: T.arrayOf(T.shape(
      EventTypes.propTypes
    )).isRequired,
    create: T.func.isRequired,
    eventActions: T.func.isRequired
  },
  defaultProps: {

  }
}

export {
  AgendaView
}
