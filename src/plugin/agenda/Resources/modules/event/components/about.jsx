import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl'
import {hasPermission} from '#/main/app/security'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {DetailsData} from '#/main/app/content/details/components/data'

import {Event as EventTypes} from '#/plugin/agenda/prop-types'
import {EventIcon} from '#/plugin/agenda/event/components/icon'
import {MODAL_EVENT_PARAMETERS} from '#/plugin/agenda/event/modals/parameters'

const EventAbout = (props) =>
  <DetailsData
    data={props.event}
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
        ].concat(props.sections)
      }, {
        icon: 'fa fa-fw fa-map-marker-alt',
        title: trans('location'),
        fields: [
          {
            name: '_locationType',
            type: 'choice',
            label: trans('type'),
            hideLabel: true,
            calculated: (event) => {
              if (event.location) {
                return 'irl'
              }

              return 'online'
            },
            options: {
              choices: {
                online: trans('online'),
                irl: trans('irl')
              }
            },
            linked: [
              {
                name: 'locationUrl',
                label: trans('url'),
                type: 'url',
                displayed: (event) => !isEmpty(event.locationUrl)
              }, {
                name: 'location',
                label: trans('location'),
                type: 'location',
                displayed: (event) => !isEmpty(event.location)
              }, {
                name: 'room',
                label: trans('room'),
                type: 'room',
                displayed: (event) => !isEmpty(event.location)
              }
            ]
          }
        ]
      }
    ]}
  >
    {props.children}

    <Toolbar
      id={`event-${props.event.id}-actions`}
      buttonName="modal-btn btn"
      actions={props.actions.concat([
        {
          name: 'edit',
          type: MODAL_BUTTON,
          label: trans('edit', {}, 'actions'),
          modal: [MODAL_EVENT_PARAMETERS, {
            event: props.event,
            onSave: props.reload
          }],
          displayed: hasPermission('edit', props.event)
        }, {
          name: 'delete',
          type: CALLBACK_BUTTON,
          label: trans('delete', {}, 'actions'),
          callback: () => props.delete(props.event).then(() => props.reload(props.event)),
          dangerous: true,
          displayed: hasPermission('delete', props.event)
        }
      ])}
    />
  </DetailsData>

EventAbout.propTypes = {
  event: T.shape(
    EventTypes.propTypes
  ).isRequired,
  sections: T.arrayOf(T.shape({
    // TODO : detail section types
  })).isRequired,
  actions: T.arrayOf(T.shape({
    // TODO : action types
  })),
  children: T.node,
  delete: T.func.isRequired,
  reload: T.func.isRequired
}

EventAbout.defaultProps = {
  actions: []
}

export {
  EventAbout
}