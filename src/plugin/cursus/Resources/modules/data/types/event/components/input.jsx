import React, {Fragment} from 'react'

import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

import {Event as EventTypes} from '#/plugin/cursus/prop-types'
import {MODAL_TRAINING_EVENTS} from '#/plugin/cursus/modals/events'
import {EventCard} from '#/plugin/cursus/event/components/card'

const EventButton = props =>
  <Button
    className="btn btn-outline-primary w-100 mt-2"
    type={MODAL_BUTTON}
    icon="fa fa-fw fa-plus"
    label={trans('add_event', {}, 'cursus')}
    disabled={props.disabled}
    modal={[MODAL_TRAINING_EVENTS, {
      title: props.title,
      selectAction: (selected) => ({
        type: CALLBACK_BUTTON,
        label: trans('select', {}, 'actions'),
        callback: () => props.onChange(selected[0])
      })
    }]}
    size={props.size}
  />

EventButton.propTypes = {
  title: T.string,
  disabled: T.bool,
  onChange: T.func.isRequired,
  size: T.string
}

const EventInput = props => {
  if (props.value) {
    return (
      <Fragment>
        <EventCard
          data={props.value}
          size="xs"
          actions={[
            {
              name: 'delete',
              type: CALLBACK_BUTTON,
              icon: 'fa fa-fw fa-trash',
              label: trans('delete', {}, 'actions'),
              dangerous: true,
              disabled: props.disabled,
              callback: () => props.onChange(null)
            }
          ]}
        />

        <EventButton
          {...props.picker}
          disabled={props.disabled}
          onChange={props.onChange}
          size={props.size}
        />
      </Fragment>
    )
  }

  return (
    <ContentPlaceholder
      icon="fa fa-calendar-day"
      title={trans('no_event', {}, 'cursus')}
      size={props.size}
    >
      <EventButton
        {...props.picker}
        size={props.size}
        disabled={props.disabled}
        onChange={props.onChange}
      />
    </ContentPlaceholder>
  )
}

implementPropTypes(EventInput, DataInputTypes, {
  value: T.shape(
    EventTypes.propTypes
  ),
  picker: T.shape({
    title: T.string
  })
}, {
  value: null
})

export {
  EventInput
}
