import {createElement, useEffect, useState} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {Event as EventTypes} from '#/plugin/agenda/prop-types'
import {getEvent} from '#/plugin/agenda/event/registry'

const EventShow = (props) => {
  const [eventShow, setEventShow] = useState({component: null})

  useEffect(() => {
    if (get(props.event, 'meta.type')) {
      getEvent(props.event.meta.type).then((eventApp) => {
        setEventShow({component: get(eventApp, 'components.show', null)})
      })
    }
  }, [get(props.event, 'meta.type')])

  if (eventShow.component) {
    return createElement(eventShow.component, {...props, id: props.event.id})
  }

  return null
}

EventShow.propTypes = {
  event: T.shape(
    EventTypes.propTypes
  ).isRequired
}

export {
  EventShow
}
