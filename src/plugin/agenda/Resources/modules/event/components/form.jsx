import React from 'react'
import {PropTypes as T} from 'prop-types'

import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {EventIcon} from '#/plugin/agenda/event/components/icon'

const EventForm = (props) =>
  <FormData
    name={props.name}
    meta={true}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'meta.type',
            type: 'type',
            label: trans('type'),
            hideLabel: true,
            calculated: (event) => ({
              icon: <EventIcon type={event.meta.type} />,
              name: trans(event.meta.type, {}, 'event'),
              description: trans(`${event.meta.type}_desc`, {}, 'event')
            })
          }
        ]
      }
    ].concat(props.sections)}
  >
    {props.children}

    <Button
      className="modal-btn btn"
      type={CALLBACK_BUTTON}
      primary={true}
      disabled={!props.saveEnabled}
      label={trans('save', {}, 'actions')}
      htmlType="submit"
      callback={() => props.save(props.name, url(
        typeof props.target === 'function' ? props.target(props.data, props.isNew) : props.target
      ), props.onSave)}
    />
  </FormData>

EventForm.propTypes = {
  name: T.string.isRequired,
  target: T.oneOfType([T.string, T.array, T.func]),
  sections: T.array,
  children: T.node,
  onSave: T.func,

  // from store
  data: T.object,
  isNew: T.bool.isRequired,
  saveEnabled: T.bool.isRequired,
  save: T.func.isRequired
}

EventForm.defaultProps = {
  sections: []
}

export {
  EventForm
}
