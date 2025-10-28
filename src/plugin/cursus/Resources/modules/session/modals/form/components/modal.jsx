import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector} from 'react-redux'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'
import omit from 'lodash/omit'
import merge from 'lodash/merge'

import {trans} from '#/main/app/intl/translation'
import {selectors as formSelectors} from '#/main/app/content/form'
import {FormModal} from '#/main/app/data/modals/form/components/modal'

import {constants} from '#/plugin/cursus/constants'
import {Course as CourseTypes, Session as SessionTypes} from '#/plugin/cursus/prop-types'

const FORM_NAME = 'trainingSessionForm'

const SessionFormModal = props => {
  let isNew = true
  let session
  let courseWorkspace
  let workspaceHelp
  if (props.session) {
    session = props.session
    isNew = false
  } else {
    if (get(props.course, 'workspace')) {
      courseWorkspace = get(props.course, 'workspace')
      if (get(courseWorkspace, 'meta.model')) {
        workspaceHelp = trans('session_workspace_model_help', {}, 'cursus')
      } else {
        workspaceHelp = trans('session_workspace_course_help', {}, 'cursus')
      }
    }

    session = merge(
      {
        workspace: !get(courseWorkspace, 'meta.model') ? courseWorkspace : null,
        course: {
          id: props.course.id,
          name: props.course.name,
          code: props.course.code,
          slug: props.course.slug
        }
      }, SessionTypes.defaultProps, omit(props.course, ['id', 'description', 'workspace', 'restrictions'].concat(get(courseWorkspace, 'meta.model') ? ['registration.tutorRole', 'registration.learnerRole'] : []))
    )
  }

  const formData = useSelector((state) => formSelectors.data(formSelectors.form(state, FORM_NAME)))

  return (
    <FormModal
      {...omit(props, 'session', 'course')}
      name={FORM_NAME}
      title={trans(isNew ? 'new_session' : 'session', {}, 'cursus')}
      subtitle={isNew ? trans('new_session_desc', {course: props.course.name}, 'cursus') : undefined}
      target={isNew ?
        ['apiv2_cursus_session_create'] :
        ['apiv2_cursus_session_update', {id: props.session.id}]
      }
      isNew={isNew}
      data={session}
      saveLabel={trans(isNew ? 'plan_training_session' : 'save_training_session', {}, 'actions')}
      definition={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'code',
              type: 'string',
              label: trans('code'),
              required: true
            }, {
              name: 'dates',
              type: 'date-range',
              label: trans('training_period', {}, 'cursus'),
              required: true
            }
          ]
        }, {
          icon: 'fa fa-fw fa-circle-info',
          title: trans('further_information'),
          fields: [
            {
              name: 'description',
              type: 'html',
              label: trans('description')
            }, {
              name: 'location',
              type: 'location',
              label: trans('location'),
              options: {multiple: false}
            }, {
              name: 'restrictions.users',
              type: 'number',
              label: trans('available_seats', {}, 'cursus'),
              options: {
                min: 0
              }
            }, {
              name: 'pricing.price',
              label: trans('price'),
              type: 'currency',
              linked: [
                {
                  name: 'pricing.description',
                  label: trans('comment'),
                  type: 'string',
                  options: {
                    long: true
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
              name: 'restrictions.hidden',
              type: 'boolean',
              label: trans('restrict_hidden'),
              help: trans('restrict_hidden_help')
            }
          ]
        }, {
          icon: 'fa fa-fw fa-user-plus',
          title: trans('registration'),
          fields: [
            {
              name: 'registration.eventRegistrationType',
              type: 'choice',
              label: trans('session_event_registration', {}, 'cursus'),
              required: true,
              options: {
                multiple: false,
                choices: constants.REGISTRATION_TYPES
              }
            }
          ]
        }, {
          icon: 'fa fa-fw fa-book',
          title: trans('workspace', {}, 'workspace'),
          description: trans('course_workspaces_help', {}, 'cursus'),
          displayed: isNew || !!get(formData, 'workspace', null),
          help: workspaceHelp,
          fields: [
            {
              name: 'workspace',
              type: 'workspace',
              label: trans(get(courseWorkspace, 'meta.model') ? 'workspace_model' : 'workspace', {}, 'workspace'),
              disabled: !isNew || !isEmpty(courseWorkspace),
              required: !isEmpty(courseWorkspace),
              calculated: () => courseWorkspace ? courseWorkspace : get(formData, 'workspace', null)
            }, {
              name: 'registration.tutorRole',
              type: 'role',
              label: trans('tutor_role', {}, 'cursus'),
              help: trans('tutor_role_help', {}, 'cursus'),
              displayed: !!get(formData, 'workspace', null),
              options: {
                picker: {
                  personal: false,
                  contextType: 'workspace',
                  contextId: get(formData, 'workspace.id', null)
                }
              }
            }, {
              name: 'registration.learnerRole',
              type: 'role',
              label: trans('learner_role', {}, 'cursus'),
              help: trans('learner_role_help', {}, 'cursus'),
              displayed: !!get(formData, 'workspace', null),
              picker: {
                personal: false,
                contextType: 'workspace',
                contextId: get(formData, 'workspace.id', null)
              }
            }
          ]
        }
      ]}
    />
  )
}

SessionFormModal.propTypes = {
  session: T.shape(
    SessionTypes.propTypes
  ),
  course: T.shape(
    CourseTypes.propTypes
  ),
  onSave: T.func
}

export {
  SessionFormModal
}
