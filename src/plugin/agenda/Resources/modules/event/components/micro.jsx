import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import tinycolor from 'tinycolor2'
import get from 'lodash/get'

import {ModalButton} from '#/main/app/buttons'

import {Event as EventTypes} from '#/plugin/agenda/prop-types'
import {EventIcon} from '#/plugin/agenda/event/components/icon'
import {MODAL_EVENT_ABOUT} from '#/plugin/agenda/event/modals/about'

const EventMicro = props => {
  let color
  if (get(props.event, 'display.color')) {
    color = tinycolor(get(props.event, 'display.color'))
  }

  return (
    <ModalButton
      className={classes('agenda-event-micro', props.className, {
        'text-light': color && color.isDark(),
        'text-dark': color && color.isLight()
      })}
      style={color ? {
        backgroundColor: color.toRgbString()
      } : undefined}
      modal={[MODAL_EVENT_ABOUT, {
        event: props.event,
        reload: props.reload
      }]}
    >
      <EventIcon className="icon-with-text-right" type={props.event.meta.type} />

      {props.event.name}
    </ModalButton>
  )
}

EventMicro.propTypes = {
  className: T.string,
  event: T.shape(
    EventTypes.propTypes
  ).isRequired,
  reload: T.func.isRequired
}


export {
  EventMicro
}