import cloneDeep from 'lodash/cloneDeep'
import set from 'lodash/set'
import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {
  selectors as formSelect,
  actions as formActions
} from '#/main/app/content/form/store'
import {actions as modalActions} from '#/main/app/overlays/modal/store'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {MODAL_DATA_LIST} from '#/main/app/modals/list'
import {ListData} from '#/main/app/content/list/containers/data'
import {ModalButton} from '#/main/app/buttons/modal/containers/button'
import {FormSections, FormSection} from '#/main/app/content/form/components/sections'

import {makeId} from '#/main/core/scaffolding/id'
import {MODAL_USERS} from '#/main/core/modals/users'
import {MODAL_GROUPS} from '#/main/core/modals/groups'

import {constants} from '#/plugin/cursus/administration/cursus/constants'
import {selectors} from '#/plugin/cursus/administration/cursus/store'
import {
  Session as SessionType,
  SessionEvent as SessionEventType
} from '#/plugin/cursus/administration/cursus/prop-types'
import {actions} from '#/plugin/cursus/administration/cursus/session/store'
import {actions as sessionEventActions} from '#/plugin/cursus/administration/cursus/session-event/store'
import {MODAL_SESSION_EVENT_FORM} from '#/plugin/cursus/administration/modals/session-event-form'
import {SessionForm} from '#/plugin/cursus/administration/cursus/session/components/form'
import {CourseList} from '#/plugin/cursus/administration/cursus/course/components/course-list'
import {SessionEventList} from '#/plugin/cursus/administration/cursus/session-event/components/session-event-list'
import {SessionUserCard} from '#/plugin/cursus/administration/cursus/session/data/components/session-user-card'
import {SessionGroupCard} from '#/plugin/cursus/administration/cursus/session/data/components/session-group-card'
import {SessionEventCard} from '#/plugin/cursus/administration/cursus/session-event/data/components/session-event-card'

const InvalidForm = (props) => props.new && props.session.id ?
  <div>
    <div className="alert alert-danger">
      {trans('session_creation_impossible_no_course', {}, 'cursus')}
    </div>
    <ModalButton
      className="btn btn-groups-primary"
      style={{marginTop: 10}}
      primary={true}
      modal={[MODAL_DATA_LIST, {
        icon: 'fa fa-fw fa-tasks',
        title: trans('select_course_for_session_creation', {}, 'cursus'),
        confirmText: trans('select', {}, 'actions'),
        name: selectors.STORE_NAME + '.courses.picker',
        definition: CourseList.definition,
        card: CourseList.card,
        fetch: {
          url: ['apiv2_cursus_course_list'],
          autoload: true
        },
        onlyId: false,
        handleSelect: (selected) => props.selectCourse(selected[0])
      }]}
    >
      <span className="fa fa-tasks icon-with-text-right" />
      {trans('select_a_course', {}, 'cursus')}
    </ModalButton>
  </div> :
  null

InvalidForm.propTypes = {
  new: T.bool.isRequired,
  session: T.shape(SessionType.propTypes).isRequired,
  selectCourse: T.func.isRequired
}

const SessionComponent = (props) => props.session && props.session.meta && props.session.meta.course ?
  <SessionForm
    name={selectors.STORE_NAME + '.sessions.current'}
    buttons={true}
    target={(session, isNew) => isNew ?
      ['apiv2_cursus_session_create'] :
      ['apiv2_cursus_session_update', {id: session.id}]
    }
    cancel={{
      type: LINK_BUTTON,
      target: `${props.path}/sessions`,
      exact: true
    }}
  >
    <FormSections level={3}>
      <FormSection
        className="embedded-list-section"
        icon="fa fa-fw fa-clock-o"
        title={trans('session_events', {}, 'cursus')}
        disabled={props.new}
        actions={[
          {
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('create_session_event', {}, 'cursus'),
            callback: () => props.openEventForm(props.session)
          }
        ]}
      >
        <ListData
          name={selectors.STORE_NAME + '.sessions.current.events'}
          fetch={{
            url: ['apiv2_cursus_session_list_events', {id: props.session.id}],
            autoload: props.session.id && !props.new
          }}
          primaryAction={(row) => ({
            type: LINK_BUTTON,
            target: `${props.path}/events/form/${row.id}`,
            label: trans('edit', {}, 'actions')
          })}
          delete={{
            url: ['apiv2_cursus_session_event_delete_bulk']
          }}
          definition={SessionEventList.definition}
          card={SessionEventCard}
        />
      </FormSection>
      <FormSection
        className="embedded-list-section"
        icon="fa fa-fw fa-user"
        title={trans('learners', {}, 'cursus')}
        disabled={props.new}
        actions={[
          {
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add_learners', {}, 'cursus'),
            modal: [MODAL_USERS, {
              title: trans('add_learners', {}, 'cursus'),
              selectAction: (selected) => ({
                type: CALLBACK_BUTTON,
                callback: () => props.addUsers(props.session.id, selected)
              })
            }]
          }
        ]}
      >
        <ListData
          name={selectors.STORE_NAME + '.sessions.current.learners'}
          fetch={{
            url: ['apiv2_cursus_session_list_users', {id: props.session.id, type: constants.LEARNER_TYPE}],
            autoload: props.session.id && !props.new
          }}
          actions={(rows) => [
            {
              type: CALLBACK_BUTTON,
              icon: 'fa fa-fw fa-plus-square',
              label: trans('invite_learners_to_session', {}, 'cursus'),
              scope: ['object', 'collection'],
              callback: () => props.inviteUsers(props.session.id, rows)
            }, {
              type: CALLBACK_BUTTON,
              icon: 'fa fa-fw fa-graduation-cap',
              label: trans('generate_session_certificates', {}, 'cursus'),
              scope: ['object', 'collection'],
              callback: () => props.generateUsersCertificates(props.session.id, rows)
            }
          ]}
          delete={{
            url: ['apiv2_cursus_session_remove_users']
          }}
          definition={[
            {
              name: 'user.firstName',
              type: 'string',
              label: trans('firstName'),
              displayed: true
            }, {
              name: 'user.lastName',
              type: 'string',
              label: trans('lastName'),
              displayed: true
            }, {
              name: 'registrationDate',
              type: 'date',
              label: trans('registration_date', {}, 'cursus'),
              displayed: true
            }
          ]}
          card={SessionUserCard}
        />
      </FormSection>
      <FormSection
        className="embedded-list-section"
        icon="fa fa-fw fa-users"
        title={trans('learners_groups', {}, 'cursus')}
        disabled={props.new}
        actions={[
          {
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add_learners_groups', {}, 'cursus'),
            modal: [MODAL_GROUPS, {
              title: trans('add_learners_groups', {}, 'cursus'),
              selectAction: (selected) => ({
                type: CALLBACK_BUTTON,
                callback: () => props.addGroups(props.session.id, selected)
              })
            }]
          }
        ]}
      >
        <ListData
          name={selectors.STORE_NAME + '.sessions.current.groups'}
          fetch={{
            url: ['apiv2_cursus_session_list_groups', {id: props.session.id, type: constants.LEARNER_TYPE}],
            autoload: props.session.id && !props.new
          }}
          actions={(rows) => [
            {
              type: CALLBACK_BUTTON,
              icon: 'fa fa-fw fa-plus-square',
              label: trans('invite_learners_to_session', {}, 'cursus'),
              scope: ['object', 'collection'],
              callback: () => props.inviteGroups(props.session.id, rows)
            }, {
              type: CALLBACK_BUTTON,
              icon: 'fa fa-fw fa-graduation-cap',
              label: trans('generate_session_certificates', {}, 'cursus'),
              scope: ['object', 'collection'],
              callback: () => props.generateGroupsCertificates(props.session.id, rows)
            }
          ]}
          delete={{
            url: ['apiv2_cursus_session_remove_groups']
          }}
          definition={[
            {
              name: 'group.name',
              type: 'string',
              label: trans('name'),
              displayed: true
            }, {
              name: 'registrationDate',
              type: 'date',
              label: trans('registration_date', {}, 'cursus'),
              displayed: true
            }
          ]}
          card={SessionGroupCard}
        />
      </FormSection>
      <FormSection
        className="embedded-list-section"
        icon="fa fa-fw fa-user"
        title={trans('tutors', {}, 'cursus')}
        disabled={props.new}
        actions={[
          {
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add_tutors', {}, 'cursus'),
            modal: [MODAL_USERS, {
              title: trans('add_tutors', {}, 'cursus'),
              selectAction: (selected) => ({
                type: CALLBACK_BUTTON,
                callback: () => props.addUsers(props.session.id, selected, constants.TEACHER_TYPE)
              })
            }]
          }
        ]}
      >
        <ListData
          name={selectors.STORE_NAME + '.sessions.current.teachers'}
          fetch={{
            url: ['apiv2_cursus_session_list_users', {id: props.session.id, type: constants.TEACHER_TYPE}],
            autoload: props.session.id && !props.new
          }}
          delete={{
            url: ['apiv2_cursus_session_remove_users']
          }}
          definition={[
            {
              name: 'user.firstName',
              type: 'string',
              label: trans('firstName'),
              displayed: true
            }, {
              name: 'user.lastName',
              type: 'string',
              label: trans('lastName'),
              displayed: true
            }, {
              name: 'registrationDate',
              type: 'date',
              label: trans('registration_date', {}, 'cursus'),
              displayed: true
            }
          ]}
          card={SessionUserCard}
        />
      </FormSection>
    </FormSections>
  </SessionForm> :
  <InvalidForm
    new={props.new}
    session={props.session}
    selectCourse={props.selectCourse}
  />

SessionComponent.propTypes = {
  path: T.string.isRequired,
  new: T.bool.isRequired,
  session: T.shape(SessionType.propTypes).isRequired,
  openEventForm: T.func.isRequired,
  selectCourse: T.func.isRequired,
  addUsers: T.func.isRequired,
  addGroups: T.func.isRequired,
  inviteUsers: T.func.isRequired,
  inviteGroups: T.func.isRequired,
  generateUsersCertificates: T.func.isRequired,
  generateGroupsCertificates: T.func.isRequired
}

const Session = connect(
  (state) => ({
    new: formSelect.isNew(formSelect.form(state, selectors.STORE_NAME + '.sessions.current')),
    session: formSelect.data(formSelect.form(state, selectors.STORE_NAME + '.sessions.current'))
  }),
  (dispatch) => ({
    selectCourse(course) {
      dispatch(formActions.updateProp(selectors.STORE_NAME + '.sessions.current', 'meta.course', course))
    },
    openEventForm(session) {
      const defaultProps = cloneDeep(SessionEventType.defaultProps)
      set(defaultProps, 'id', makeId())
      set(defaultProps, 'meta.session.id', session.id)
      set(defaultProps, 'registration.registrationType', session.registration.eventRegistrationType)
      set(defaultProps, 'restrictions.dates', session.restrictions.dates)
      dispatch(sessionEventActions.open(selectors.STORE_NAME + '.events.current', defaultProps))
      dispatch(modalActions.showModal(MODAL_SESSION_EVENT_FORM))
    },
    addUsers(sessionId, users, type = constants.LEARNER_TYPE) {
      dispatch(actions.addUsers(sessionId, users, type))
    },
    addGroups(sessionId, groups) {
      dispatch(actions.addGroups(sessionId, groups))
    },
    inviteUsers(sessionId, sessionUsers) {
      const users = sessionUsers.map(function (sessionUser) {
        return sessionUser['user']
      }, sessionUsers)
      dispatch(actions.inviteUsers(sessionId, users))
    },
    inviteGroups(sessionId, sessionGroups) {
      const groups = sessionGroups.map(function (sessionGroup) {
        return sessionGroup['group']
      }, sessionGroups)
      dispatch(actions.inviteGroups(sessionId, groups))
    },
    generateUsersCertificates(sessionId, sessionUsers) {
      const users = sessionUsers.map(function (sessionUser) {
        return sessionUser['user']
      }, sessionUsers)
      dispatch(actions.generateUsersCertificates(sessionId, users))
    },
    generateGroupsCertificates(sessionId, sessionGroups) {
      const groups = sessionGroups.map(function (sessionGroup) {
        return sessionGroup['group']
      }, sessionGroups)
      dispatch(actions.generateGroupsCertificates(sessionId, groups))
    }
  })
)(SessionComponent)

export {
  Session
}
