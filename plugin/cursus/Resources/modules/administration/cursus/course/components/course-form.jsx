import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {selectors as formSelect} from '#/main/app/content/form/store/selectors'
// import {actions as formActions} from '#/main/app/content/form/store/actions'
// import {actions as modalActions} from '#/main/app/overlay/modal/store'
import {LINK_BUTTON} from '#/main/app/buttons'
// import {MODAL_DATA_LIST} from '#/main/app/modals/list'
import {FormData} from '#/main/app/content/form/containers/data'
// import {ListData} from '#/main/app/content/list/containers/data'

import {trans} from '#/main/core/translation'
// import {FormSections, FormSection} from '#/main/core/layout/form/components/form-sections'

import {Course as CourseType} from '#/plugin/cursus/administration/cursus/prop-types'
// import {GroupList} from '#/main/core/administration/user/group/components/group-list'
// import {UserList} from '#/main/core/administration/user/user/components/user-list'

const CourseFormComponent = () =>
  <FormData
    level={3}
    name="courses.current"
    buttons={true}
    target={(course, isNew) => isNew ?
      ['apiv2_cursus_course_create'] :
      ['apiv2_cursus_course_update', {id: course.id}]
    }
    cancel={{
      type: LINK_BUTTON,
      target: '/courses',
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
            name: 'meta.withSessionEvent',
            type: 'boolean',
            label: trans('with_session_event', {}, 'cursus'),
            required: true
          }, {
            name: 'meta.tutorRoleName',
            type: 'string',
            label: trans('tutor_role', {}, 'cursus'),
            displayed: (course) => !course.meta || (!course.meta.workspace && !course.meta.workspaceModel),
            required: true
          }, {
            name: 'meta.learnerRoleName',
            type: 'string',
            label: trans('learner_role', {}, 'cursus'),
            displayed: (course) => !course.meta || (!course.meta.workspace && !course.meta.workspaceModel),
            required: true
          }, {
            name: 'meta.workspace',
            type: 'string',
            label: trans('workspace')
          }, {
            name: 'meta.workspaceModel',
            type: 'string',
            label: trans('workspace_model')
          }, {
            name: 'meta.icon',
            type: 'file',
            label: trans('icon')
          }, {
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
            label: trans('public_registration'),
            required: true
          }, {
            name: 'registration.publicUnregistration',
            type: 'boolean',
            label: trans('public_unregistration'),
            required: true
          }, {
            name: 'registration.registrationValidation',
            type: 'boolean',
            label: trans('registration_validation', {}, 'cursus'),
            required: true
          }, {
            name: 'registration.userValidation',
            type: 'boolean',
            label: trans('user_validation', {}, 'cursus'),
            required: true
          }, {
            name: 'registration.organizationValidation',
            type: 'boolean',
            label: trans('organization_validation', {}, 'cursus'),
            required: true
          }
        ]
      }, {
        icon: 'fa fa-fw fa-key',
        title: trans('restrictions'),
        fields: [
          {
            name: 'restrictions.maxUsers',
            type: 'number',
            label: trans('maxUsers'),
            options: {
              min: 0
            }
          }
        ]
      }
    ]}
  >
  </FormData>

CourseFormComponent.propTypes = {
  new: T.bool.isRequired,
  course: T.shape(CourseType.propTypes).isRequired
}

const CourseForm = connect(
  state => ({
    new: formSelect.isNew(formSelect.form(state, 'courses.current')),
    course: formSelect.data(formSelect.form(state, 'courses.current'))
  })
  // dispatch => ({
  //   updateProp(propName, propValue) {
  //     dispatch(formActions.updateProp('roles.current', propName, propValue))
  //   },
  //   pickUsers(roleId) {
  //     dispatch(modalActions.showModal(MODAL_DATA_LIST, {
  //       icon: 'fa fa-fw fa-user',
  //       title: trans('add_users'),
  //       confirmText: trans('add'),
  //       name: 'users.picker',
  //       definition: UserList.definition,
  //       card: UserList.card,
  //       fetch: {
  //         url: ['apiv2_user_list'],
  //         autoload: true
  //       },
  //       handleSelect: (selected) => dispatch(actions.addUsers(roleId, selected))
  //     }))
  //   },
  //   pickGroups(roleId){
  //     dispatch(modalActions.showModal(MODAL_DATA_LIST, {
  //       icon: 'fa fa-fw fa-users',
  //       title: trans('add_groups'),
  //       confirmText: trans('add'),
  //       name: 'groups.picker',
  //       definition: GroupList.definition,
  //       card: GroupList.card,
  //       fetch: {
  //         url: ['apiv2_group_list'],
  //         autoload: true
  //       },
  //       handleSelect: (selected) => dispatch(actions.addGroups(roleId, selected))
  //     }))
  //   }
  // })
)(CourseFormComponent)

export {
  CourseForm
}
