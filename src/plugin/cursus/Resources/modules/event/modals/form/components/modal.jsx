import React, {useCallback} from 'react'
import {PropTypes as T} from 'prop-types'
import {useDispatch, useSelector} from 'react-redux'
import omit from 'lodash/omit'
import merge from 'lodash/merge'

import {trans} from '#/main/app/intl/translation'
import {FormModal} from '#/main/app/data/modals/form/components/modal'
import {actions as formActions} from '#/main/app/content/form'
import {constants} from '#/plugin/cursus/constants'
import {Event as EventTypes, Session as SessionTypes} from '#/plugin/cursus/prop-types'
import {selectors as contextSelectors} from '#/main/app/context'

const FORM_NAME = 'trainingEventForm'

const EventFormModal = props => {
  const dispatch = useDispatch()

  const contextType = useSelector(contextSelectors.type)
  const contextData = useSelector(contextSelectors.data)

  const update = useCallback((prop, value) => {
    dispatch(formActions.updateProp(FORM_NAME, prop, value))
  }, [FORM_NAME])

  let formData
  if (props.event) {
    formData = props.event
  } else if (props.isNew && props.session) {
    formData = merge(
      {
        session: {
          id: props.session.id,
          name: props.session.name,
          code: props.session.code,
          slug: props.session.slug,
          restrictions: props.session.restrictions
        }
      }, EventTypes.defaultProps, omit(props.session, 'id', 'name', 'code')
    )
  }

  return (
    <FormModal
      title={trans(props.isNew ? 'new_event' : 'session_event', {}, 'cursus')}
      subtitle={props.isNew && props.session ? trans('new_event_desc', {session: props.session.name}, 'cursus') : undefined}
      {...omit(props, 'event', 'session')}
      name={FORM_NAME}
      target={props.isNew ?
        ['apiv2_training_event_create'] :
        ['apiv2_training_event_update', {id: props.event.id}]
      }
      isNew={props.isNew}
      data={formData}
      saveLabel={trans(props.isNew ? 'plan_training_event' : 'save_training_event', {}, 'actions')}
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
                update('start', datesRange[0])
                update('end', datesRange[1])
              },
              options: {
                time: true
              }
            }, {
              name: 'session',
              type: 'training_session',
              label: trans('session', {}, 'cursus'),
              required: true,
              displayed: !props.session,
              options: {
                multiple: false,
                picker: {
                  url: ['apiv2_cursus_session_context_list', {context: contextType, contextId: contextData ? contextData.id : null}]
                }
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
            }, {
              name: 'restrictions.users',
              type: 'number',
              label: trans('users_count'),
              options: {
                min: 0
              },
              displayed: (event) => event.registration && constants.REGISTRATION_AUTO !== event.registration.registrationType
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
              name: 'presenceTemplate',
              type: 'template',
              label: trans('training_event_presence', {}, 'template'),
              options: {
                templateType: 'training_event_presence'
              }
            }
          ]
        }, {
          icon: 'fa fa-fw fa-user-plus',
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
              label: trans('registration_send_mail', {}, 'cursus'),
              linked: [
                {
                  name: 'invitationTemplate',
                  type: 'template',
                  label: trans('training_event_invitation', {}, 'template'),
                  displayed: (event) => event.registration ? event.registration.mail : false,
                  options: {
                    templateType: 'training_event_invitation'
                  }
                }
              ]
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
  session: T.shape(
    SessionTypes.propTypes
  ),
  onSave: T.func.isRequired
}

export {
  EventFormModal
}
