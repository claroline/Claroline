import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {Routes} from '#/main/app/router/components/routes'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ContentTabs} from '#/main/app/content/components/tabs'

import {Course as CourseTypes} from '#/plugin/cursus/course/prop-types'

import {CourseAbout} from '#/plugin/cursus/course/components/about'
import {CourseParticipants} from '#/plugin/cursus/course/components/participants'
import {CourseSessions} from '#/plugin/cursus/course/containers/sessions'

const CourseDetails = (props) =>
  <Fragment>
    <header className="row content-heading">
      <ContentTabs
        backAction={{
          type: LINK_BUTTON,
          target: props.path,
          exact: true
        }}
        sections={[
          {
            name: 'about',
            type: LINK_BUTTON,
            icon: 'fa fa-fw fa-info',
            label: trans('about'),
            target: `${props.path}/${props.course.slug}${props.activeSession ? '/'+props.activeSession.id : null}`,
            exact: true
          }, {
            name: 'participants',
            type: LINK_BUTTON,
            icon: 'fa fa-fw fa-users',
            label: trans('Participants', {}, 'cursus'),
            target: `${props.path}/${props.course.slug}${props.activeSession ? '/'+props.activeSession.id : null}/participants`
          }, {
            name: 'sessions',
            type: LINK_BUTTON,
            icon: 'fa fa-fw fa-calendar-week',
            label: trans('Sessions', {}, 'cursus'),
            target: `${props.path}/${props.course.slug}${props.activeSession ? '/'+props.activeSession.id : null}/sessions`
          }, {
            name: 'events',
            type: LINK_BUTTON,
            icon: 'fa fa-fw fa-clock',
            label: trans('session_events', {}, 'cursus'),
            target: `${props.path}/${props.course.slug}${props.activeSession ? '/'+props.activeSession.id : null}/events`
          }
        ]}
      />
    </header>

    <Routes
      path={props.path+'/'+props.course.slug+(props.activeSession ? '/'+props.activeSession.id : null)}
      routes={[
        {
          path: '/',
          exact: true,
          render() {
            return (
              <CourseAbout
                path={props.path}
                course={props.course}
                activeSession={props.activeSession}
                availableSessions={props.availableSessions}
              />
            )
          }
        }, {
          path: '/participants',
          render() {
            return (
              <CourseParticipants
                path={props.path}
                course={props.course}
              />
            )
          }
        }, {
          path: '/sessions',
          render() {
            return (
              <CourseSessions
                path={props.path}
                course={props.course}
              />
            )
          }
        }
      ]}
    />
  </Fragment>

CourseDetails.propTypes = {
  path: T.string.isRequired,
  course: T.shape(
    CourseTypes.propTypes
  ).isRequired,
  activeSession: T.shape({
    id: T.string.isRequired
  }),
  availableSessions: T.arrayOf(T.shape({
    // TODO : propTypes
  }))
}

export {
  CourseDetails
}
