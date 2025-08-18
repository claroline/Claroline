import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {FormContent} from '#/main/app/content/form/containers/content'

import {EventForm} from '#/plugin/agenda/event/containers/form'

const TaskForm = (props) =>
  <EventForm
    flush={props.flush}
    name={props.name}
    target={(task, isNew) => isNew ? ['apiv2_task_create']: ['apiv2_task_update', {id: task.id}]}
    onSave={props.onSave}
  >
    <FormContent
      flush={props.flush}
      name={props.name}
      definition={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'poster',
              type: 'poster',
              label: trans('poster'),
              hideLabel: true
            }, {
              name: 'name',
              type: 'string',
              label: trans('name'),
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
          icon: 'fa fa-fw fa-circle-info',
          title: trans('information'),
          fields: [
            {
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
                  displayed: (event) => event.location || 'irl' === event._locationType,
                  options: {multiple: false}
                }
              ]
            }
          ]
        }, {
          icon: 'fa fa-fw fa-desktop',
          title: trans('display_parameters'),
          fields: [
            {
              name: 'display.color',
              type: 'color',
              label: trans('color')
            }
          ]
        }
      ]}
    />
  </EventForm>

TaskForm.propTypes = {
  flush: T.bool,
  name: T.string.isRequired,
  event: T.object,
  update: T.func.isRequired,
  onSave: T.func
}

export {
  TaskForm
}
