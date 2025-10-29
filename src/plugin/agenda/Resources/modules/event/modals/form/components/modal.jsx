import {createElement, useEffect, useState} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {Event as EventTypes} from '#/plugin/agenda/prop-types'
import {getEvent} from '#/plugin/agenda/event/registry'

const EventFormModal = (props) => {
  const [formModal, setFormModal] = useState({modal: null})

  useEffect(() => {
    if (get(props.event, 'meta.type')) {
      getEvent(props.event.meta.type).then((eventApp) => {
        setFormModal({modal: get(eventApp, 'modals.form', null)})
      })
    }
  }, [get(props.event, 'meta.type')])

  if (formModal.modal) {
    return createElement(formModal.modal, {...props})
  }

  return null
}

EventFormModal.propTypes = {
  event: T.shape(
    EventTypes.propTypes
  ).isRequired,
  onSave: T.func.isRequired,
  fadeModal: T.func.isRequired
}

export {
  EventFormModal
}
