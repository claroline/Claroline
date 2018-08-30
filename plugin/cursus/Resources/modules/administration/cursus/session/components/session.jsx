import cloneDeep from 'lodash/cloneDeep'
import set from 'lodash/set'
import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {
  selectors as formSelect,
  actions as formActions
} from '#/main/app/content/form/store'
import {actions as modalActions} from '#/main/app/overlay/modal/store'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {MODAL_DATA_LIST} from '#/main/app/modals/list'
import {ListData} from '#/main/app/content/list/containers/data'
import {CallbackButton} from '#/main/app/buttons/callback/components/button'

import {trans} from '#/main/core/translation'
import {makeId} from '#/main/core/scaffolding/id'
import {FormSections, FormSection} from '#/main/core/layout/form/components/form-sections'

import {
  Session as SessionType,
  SessionEvent as SessionEventType
} from '#/plugin/cursus/administration/cursus/prop-types'
import {actions as sessionEventActions} from '#/plugin/cursus/administration/cursus/session-event/store'
import {MODAL_SESSION_EVENT_FORM} from '#/plugin/cursus/administration/modals/session-event-form'
import {SessionForm} from '#/plugin/cursus/administration/cursus/session/components/form'
import {CourseList} from '#/plugin/cursus/administration/cursus/course/components/course-list'
import {SessionEventList} from '#/plugin/cursus/administration/cursus/session-event/components/session-event-list'

const InvalidForm = (props) => props.new && props.session.id ?
  <div>
    <div className="alert alert-danger">
      {trans('session_creation_impossible_no_course', {}, 'cursus')}
    </div>
    <CallbackButton
      className="btn btn-block"
      primary={true}
      callback={() => props.selectCourse()}
    >
      <span className="fa fa-tasks" />
      {trans('select_a_course', {}, 'cursus')}
    </CallbackButton>
  </div> :
  null

InvalidForm.propTypes = {
  new: T.bool.isRequired,
  session: T.shape(SessionType.propTypes).isRequired,
  selectCourse: T.func.isRequired
}

const SessionComponent = (props) => props.session && props.session.meta && props.session.meta.course ?
  <SessionForm
    name="sessions.current"
    buttons={true}
    target={(session, isNew) => isNew ?
      ['apiv2_cursus_session_create'] :
      ['apiv2_cursus_session_update', {id: session.id}]
    }
    cancel={{
      type: LINK_BUTTON,
      target: '/sessions',
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
          name="sessions.current.events"
          fetch={{
            url: ['apiv2_cursus_session_list_events', {id: props.session.id}],
            autoload: props.session.id && !props.new
          }}
          primaryAction={SessionEventList.open}
          delete={{
            url: ['apiv2_cursus_session_event_delete_bulk']
          }}
          definition={SessionEventList.definition}
          card={SessionEventList.card}
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
  new: T.bool.isRequired,
  session: T.shape(SessionType.propTypes).isRequired,
  openEventForm: T.func.isRequired,
  selectCourse: T.func.isRequired
}

const Session = connect(
  (state) => ({
    new: formSelect.isNew(formSelect.form(state, 'sessions.current')),
    session: formSelect.data(formSelect.form(state, 'sessions.current'))
  }),
  (dispatch) => ({
    selectCourse() {
      dispatch(modalActions.showModal(MODAL_DATA_LIST, {
        icon: 'fa fa-fw fa-tasks',
        title: trans('select_course_for_session_creation', {}, 'cursus'),
        confirmText: trans('select', {}, 'actions'),
        name: 'courses.picker',
        definition: CourseList.definition,
        card: CourseList.card,
        fetch: {
          url: ['apiv2_cursus_course_list'],
          autoload: true
        },
        handleSelect: (selected) => {
          dispatch(formActions.updateProp('sessions.current', 'meta.course.id', selected[0]))
        }
      }))
    },
    openEventForm(session) {
      const defaultProps = cloneDeep(SessionEventType.defaultProps)
      set(defaultProps, 'id', makeId())
      set(defaultProps, 'meta.session.id', session.id)
      set(defaultProps, 'registration.registrationType', session.registration.eventRegistrationType)
      set(defaultProps, 'restrictions.dates', session.restrictions.dates)
      dispatch(sessionEventActions.open('events.current', defaultProps))
      dispatch(modalActions.showModal(MODAL_SESSION_EVENT_FORM))
    }
  })
)(SessionComponent)

export {
  Session
}
