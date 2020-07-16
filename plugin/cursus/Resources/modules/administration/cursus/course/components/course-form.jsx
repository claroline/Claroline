import cloneDeep from 'lodash/cloneDeep'
import set from 'lodash/set'
import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {selectors as formSelect} from '#/main/app/content/form/store/selectors'
import {actions as modalActions} from '#/main/app/overlays/modal/store'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {MODAL_DATA_LIST} from '#/main/app/modals/list'
import {FormData} from '#/main/app/content/form/containers/data'
import {ListData} from '#/main/app/content/list/containers/data'

import {trans} from '#/main/app/intl/translation'
import {makeId} from '#/main/core/scaffolding/id'
import {now, nowAdd} from '#/main/app/intl/date'
import {FormSections, FormSection} from '#/main/app/content/form/components/sections'
import {OrganizationList} from '#/main/core/administration/community/organization/components/organization-list'

import {
  Course as CourseType,
  Session as SessionType,
  Parameters as ParametersType
} from '#/plugin/cursus/administration/cursus/prop-types'
import {selectors} from '#/plugin/cursus/administration/cursus/store'
import {actions} from '#/plugin/cursus/administration/cursus/course/store'
import {actions as sessionActions} from '#/plugin/cursus/administration/cursus/session/store'
import {MODAL_SESSION_FORM} from '#/plugin/cursus/administration/modals/session-form'
import {SessionList} from '#/plugin/cursus/administration/cursus/session/components/session-list'

const CourseFormComponent = (props) =>
  <FormData
    level={3}
    name={selectors.STORE_NAME + '.courses.current'}
    buttons={true}
    target={(course, isNew) => isNew ?
      ['apiv2_cursus_course_create'] :
      ['apiv2_cursus_course_update', {id: course.id}]
    }
    cancel={{
      type: LINK_BUTTON,
      target: `${props.path}/courses`,
      exact: true
    }}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'title',
            type: 'string',
            label: trans('title'),
            required: true
          }, {
            name: 'code',
            type: 'string',
            label: trans('code'),
            required: true
          }, {
            name: 'description',
            type: 'html',
            label: trans('description')
          }
        ]
      }, {
        icon: 'fa fa-fw fa-cogs',
        title: trans('parameters'),
        fields: [
          {
            name: 'workspace',
            type: 'workspace',
            label: trans('workspace')
          }, {
            name: 'workspaceModel',
            type: 'workspace',
            label: trans('workspace_model'),
            options: {
              picker: {
                model: true,
                title: trans('workspace_model')
              }
            },
            displayed: (course) => !course.workspace
          }, {
            name: 'meta.tutorRoleName',
            type: 'string',
            label: trans('tutor_role', {}, 'cursus'),
            displayed: (course) => !course.workspace && !course.workspaceModel
          }, {
            name: 'meta.learnerRoleName',
            type: 'string',
            label: trans('learner_role', {}, 'cursus'),
            displayed: (course) => !course.workspace && !course.workspaceModel
          }, {
            name: 'meta.tutorRoleName',
            type: 'choice',
            label: trans('tutor_role', {}, 'cursus'),
            displayed: (course) => course.workspace && course.workspace.roles,
            required: true,
            options: {
              condensed: true,
              multiple: false,
              choices: props.course && props.course.workspace && props.course.workspace.roles ?
                props.course.workspace.roles.reduce((acc, role) => {
                  if (2 === role.type) {
                    acc[role.translationKey] = trans(role.translationKey)
                  }

                  return acc
                }, {}) :
                {}
            }
          }, {
            name: 'meta.learnerRoleName',
            type: 'choice',
            label: trans('learner_role', {}, 'cursus'),
            displayed: (course) => course.meta && course.workspace && course.workspace.roles,
            required: true,
            options: {
              condensed: true,
              multiple: false,
              choices: props.course && props.course.meta && props.course.workspace && props.course.workspace.roles ?
                props.course.workspace.roles.reduce((acc, role) => {
                  if (2 === role.type) {
                    acc[role.translationKey] = trans(role.translationKey)
                  }

                  return acc
                }, {}) :
                {}
            }
          }, {
            name: 'meta.tutorRoleName',
            type: 'choice',
            label: trans('tutor_role', {}, 'cursus'),
            displayed: (course) => course.meta && !course.workspace && course.workspaceModel && course.workspaceModel.roles,
            required: true,
            options: {
              condensed: true,
              multiple: false,
              choices: props.course && props.course.meta && props.course.workspaceModel && props.course.workspaceModel.roles ?
                props.course.workspaceModel.roles.reduce((acc, role) => {
                  if (2 === role.type) {
                    acc[role.translationKey] = trans(role.translationKey)
                  }

                  return acc
                }, {}) :
                {}
            }
          }, {
            name: 'meta.learnerRoleName',
            type: 'choice',
            label: trans('learner_role', {}, 'cursus'),
            displayed: (course) => course.meta && !course.workspace && course.workspaceModel && course.workspaceModel.roles,
            required: true,
            options: {
              condensed: true,
              multiple: false,
              choices: props.course && props.course.meta && props.course.workspaceModel && props.course.workspaceModel.roles ?
                props.course.workspaceModel.roles.reduce((acc, role) => {
                  if (2 === role.type) {
                    acc[role.translationKey] = trans(role.translationKey)
                  }

                  return acc
                }, {}) :
                {}
            }
          }, {
          //   name: 'meta.icon',
          //   type: 'file',
          //   label: trans('icon')
          // }, {
            name: 'meta.defaultSessionDuration',
            type: 'number',
            label: trans('default_session_duration_label', {}, 'cursus'),
            required: true,
            options: {
              min: 0
            }
          }, {
            name: 'meta.order',
            type: 'number',
            label: trans('order'),
            required: true,
            options: {
              min: 0
            }
          }
        ]
      }, {
        icon: 'fa fa-fw fa-sign-in',
        title: trans('registration'),
        fields: [
          {
            name: 'registration.publicRegistration',
            type: 'boolean',
            label: trans('public_registration')
          }, {
            name: 'registration.publicUnregistration',
            type: 'boolean',
            label: trans('public_unregistration')
          }, {
            name: 'registration.registrationValidation',
            type: 'boolean',
            label: trans('registration_validation', {}, 'cursus')
          }, {
            name: 'registration.userValidation',
            type: 'boolean',
            label: trans('user_validation', {}, 'cursus')
          }, {
            name: 'registration.organizationValidation',
            type: 'boolean',
            label: trans('organization_validation', {}, 'cursus')
          }
        ]
      }, {
        icon: 'fa fa-fw fa-key',
        title: trans('access_restrictions'),
        fields: [
          {
            name: 'restrictions.users',
            type: 'number',
            label: trans('users_count'),
            options: {
              min: 0
            }
          }
        ]
      }
    ]}
  >
    <FormSections level={3}>
      <FormSection
        className="embedded-list-section"
        icon="fa fa-fw fa-building"
        title={trans('organizations')}
        disabled={props.new}
        actions={[
          {
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add_organizations'),
            modal: [MODAL_DATA_LIST, {
              icon: 'fa fa-fw fa-building',
              title: trans('add_organizations'),
              confirmText: trans('add'),
              name: selectors.STORE_NAME + '.courses.current.organizations.picker',
              definition: OrganizationList.definition,
              card: OrganizationList.card,
              fetch: {
                url: ['apiv2_organization_list'],
                autoload: true
              },
              handleSelect: (selected) => props.addOrganizations(props.course.id, selected)
            }]
          }
        ]}
      >
        <ListData
          name={selectors.STORE_NAME + '.courses.current.organizations.list'}
          fetch={{
            url: ['apiv2_cursus_course_list_organizations', {id: props.course.id}],
            autoload: props.course.id && !props.new
          }}
          primaryAction={OrganizationList.open}
          delete={{
            url: ['apiv2_cursus_course_remove_organizations', {id: props.course.id}]
          }}
          definition={OrganizationList.definition}
          card={OrganizationList.card}
        />
      </FormSection>
      <FormSection
        className="embedded-list-section"
        icon="fa fa-fw fa-cubes"
        title={trans('sessions', {}, 'cursus')}
        disabled={props.new}
        actions={[
          {
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('create_session', {}, 'cursus'),
            callback: () => props.openSessionForm(props.course, props.parameters.session_default_duration, props.parameters.session_default_total)
          }
        ]}
      >
        <ListData
          name={selectors.STORE_NAME + '.courses.current.sessions'}
          fetch={{
            url: ['apiv2_cursus_course_list_sessions', {id: props.course.id}],
            autoload: props.course.id && !props.new
          }}
          primaryAction={(row) => ({
            type: LINK_BUTTON,
            target: `${props.path}/sessions/form/${row.id}`,
            label: trans('edit', {}, 'actions')
          })}
          delete={{
            url: ['apiv2_cursus_session_delete_bulk']
          }}
          definition={SessionList.definition}
          card={SessionList.card}
        />
      </FormSection>
    </FormSections>
  </FormData>

CourseFormComponent.propTypes = {
  path: T.string.isRequired,
  new: T.bool.isRequired,
  parameters: T.shape(ParametersType.propTypes).isRequired,
  course: T.shape(CourseType.propTypes).isRequired,
  addOrganizations: T.func.isRequired,
  openSessionForm: T.func.isRequired
}

const CourseForm = connect(
  state => ({
    parameters: selectors.parameters(state),
    new: formSelect.isNew(formSelect.form(state, selectors.STORE_NAME + '.courses.current')),
    course: formSelect.data(formSelect.form(state, selectors.STORE_NAME + '.courses.current'))
  }),
  dispatch => ({
    addOrganizations(courseId, organizationIds) {
      dispatch(actions.addOrganizations(courseId, organizationIds))
    },
    openSessionForm(course, duration, total) {
      const defaultProps = cloneDeep(SessionType.defaultProps)
      const dates = [now(false), nowAdd({days: duration ? duration : 1})]
      set(defaultProps, 'id', makeId())
      set(defaultProps, 'meta.course', course)
      set(defaultProps, 'meta.total', total)
      set(defaultProps, 'restrictions.dates', dates)
      dispatch(sessionActions.open(selectors.STORE_NAME + '.sessions.current', defaultProps))
      dispatch(modalActions.showModal(MODAL_SESSION_FORM))
    }
  })
)(CourseFormComponent)

export {
  CourseForm
}
