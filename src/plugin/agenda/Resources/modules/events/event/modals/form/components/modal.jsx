import React, {useCallback} from 'react'
import {PropTypes as T} from 'prop-types'
import {useDispatch} from 'react-redux'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {FormModal} from '#/main/app/data/modals/form/components/modal'
import {actions as formActions} from '#/main/app/content/form'

import {Event as EventTypes} from '#/plugin/agenda/prop-types'

const FORM_NAME = 'agendaEventForm'

const EventFormModal = props => {
  const dispatch = useDispatch()

  const update = useCallback((prop, value) => {
    dispatch(formActions.updateProp(FORM_NAME, prop, value))
  }, [FORM_NAME])

  return (
    <FormModal
      title={trans(props.isNew ? 'new_event' : 'event', {}, 'agenda')}
      subtitle={props.isNew && props.session ? trans('new_event_desc', {}, 'agenda') : undefined}
      {...omit(props, 'event')}
      name={FORM_NAME}
      target={props.isNew ?
        ['apiv2_event_create'] :
        ['apiv2_event_update', {id: props.event.id}]
      }
      isNew={props.isNew}
      data={props.event}
      saveLabel={trans(props.isNew ? 'add_event' : 'save_event', {}, 'actions')}
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
                update('start', datesRange[0])
                update('end', datesRange[1])
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
            }, {
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
                  update('locationUrl', null)
                } else {
                  update('location', null)
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
            }, {
              name: 'invitationTemplate',
              type: 'template',
              label: trans('event_invitation', {}, 'template'),
              options: {
                templateType: 'event_invitation'
              }
            }
          ]
        }
      ]}
    />
  )
}

EventFormModal.propTypes = {
  isNew: T.bool,
  event: T.shape(
    EventTypes.propTypes
  ),
  onSave: T.func.isRequired
}

export {
  EventFormModal
}
