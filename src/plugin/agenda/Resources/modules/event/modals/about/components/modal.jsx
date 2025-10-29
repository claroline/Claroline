import {createElement, useEffect, useState} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {Event as EventTypes} from '#/plugin/agenda/prop-types'
import {getEvent} from '#/plugin/agenda/event/registry'

const EventAboutModal = (props) => {
  const [aboutModal, setAboutModal] = useState({modal: null})

  useEffect(() => {
    if (get(props.event, 'meta.type')) {
      getEvent(props.event.meta.type).then((eventApp) => {
        setAboutModal({modal: get(eventApp, 'modals.about', null)})
      })
    }
  }, [get(props.event, 'meta.type')])

  if (aboutModal.modal) {
    return createElement(aboutModal.modal, {...props})
  }

  return null
}

EventAboutModal.propTypes = {
  event: T.shape(
    EventTypes.propTypes
  ).isRequired,
  reload: T.func.isRequired,
  fadeModal: T.func.isRequired
}

export {
  EventAboutModal
}
