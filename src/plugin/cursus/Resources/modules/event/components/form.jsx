import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'

import {constants} from '#/plugin/cursus/constants'

const EventForm = (props) =>
  <FormData
    {...props}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'name',
            type: 'string',
            label: trans('name'),
            required: true
          }, {
            name: 'code',
            type: 'string',
            label: trans('code'),
            required: true
          }, {
            name: 'dates',
            type: 'date-range',
            label: trans('date'),
            required: true,
            calculated: (event) => [event.start || null, event.end || null],
            onChange: (datesRange) => {
              props.update('start', datesRange[0])
              props.update('end', datesRange[1])
            },
            options: {
              time: true
            }
          }
        ]
      }, {
        icon: 'fa fa-fw fa-info',
        title: trans('information'),
        fields: [
          {
            name: 'session',
            label: trans('session', {}, 'cursus'),
            type: 'training_session',
            required: true,
            disabled: (data) => !isEmpty(data.session)
          }, {
            name: 'description',
            type: 'html',
            label: trans('description')
          }
        ]
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
              if (event.location || 'irl' === event._locationType) {
                return 'irl'
              }

              return 'online'
            },
            onChange: (value) => {
              if ('irl' === value) {
                props.update('locationUrl', null)
              } else {
                props.update('location', null)
                props.update('room', null)
              }
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
                displayed: (event) => event.locationUrl || !event._locationType || 'online' === event._locationType
              }, {
                name: 'location',
                label: trans('location'),
                type: 'location',
                displayed: (event) => event.location || 'irl' === event._locationType
              }, {
                name: 'room',
                label: trans('room'),
                type: 'room',
                displayed: (event) => !isEmpty(event.location),
                options: {
                  picker: {
                    filters: [{property: 'location', value: get(props.event, 'location.id'), locked: true}]
                  }
                }
              }
            ]
          }
        ]
      }, {
        icon: 'fa fa-fw fa-desktop',
        title: trans('display_parameters'),
        fields: [
          {
            name: 'poster',
            type: 'image',
            label: trans('poster')
          }, {
            name: 'thumbnail',
            type: 'image',
            label: trans('thumbnail')
          }, {
            name: 'display.color',
            type: 'color',
            label: trans('color')
          }, {
            name: 'presenceTemplate',
            type: 'template',
            label: trans('training_event_presence', {}, 'template'),
            options: {
              picker: {
                filters: [{property: 'typeName', value: 'training_event_presence', locked: true}]
              }
            }
          }
        ]
      }, {
        icon: 'fa fa-fw fa-sign-in',
        title: trans('registration'),
        fields: [
          {
            name: 'registration.registrationType',
            type: 'choice',
            label: trans('session_event_registration', {}, 'cursus'),
            required: true,
            options: {
              multiple: false,
              choices: constants.REGISTRATION_TYPES
            }
          }, {
            name: 'registration.mail',
            type: 'boolean',
            label: trans('registration_send_mail', {}, 'cursus')
          }
        ]
      }, {
        icon: 'fa fa-fw fa-key',
        title: trans('restrictions'),
        fields: [
          {
            name: 'restrictions.users',
            type: 'number',
            label: trans('users_count'),
            options: {
              min: 0
            },
            displayed: (event) => event.registration && constants.REGISTRATION_AUTO !== event.registration.registrationType
          }
        ]
      }
    ]}
  >
    {props.children}
  </FormData>

EventForm.propTypes = {
  name: T.string.isRequired,
  event: T.object,
  update: T.func.isRequired,
  children: T.any
}

export {
  EventForm
}
