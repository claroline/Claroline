import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {matchPath, Routes} from '#/main/app/router'

import {ToolPage} from '#/main/core/tool/containers/page'

import {Parameters as ParametersType} from '#/plugin/cursus/administration/cursus/prop-types'
import {SessionTab} from '#/plugin/cursus/administration/cursus/session/components/session-tab'
import {CourseTab} from '#/plugin/cursus/administration/cursus/course/components/course-tab'
import {CursusTab} from '#/plugin/cursus/administration/cursus/cursus/components/cursus-tab'
import {SessionEventTab} from '#/plugin/cursus/administration/cursus/session-event/components/session-event-tab'
import {Queues} from '#/plugin/cursus/administration/cursus/queue/components/queues'
import {Parameters} from '#/plugin/cursus/administration/cursus/components/parameters'

const CursusTool = (props) =>
  <ToolPage
    actions={[
      {
        name: 'new_course',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('create_course', {}, 'cursus'),
        target: `${props.path}/courses/form`,
        primary: true,
        displayed: !!matchPath(props.location.pathname, {path: `${props.path}/courses`, exact: true})
      }, {
        name: 'new_session',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('create_session', {}, 'cursus'),
        target: `${props.path}/sessions/form`,
        primary: true,
        displayed: !!matchPath(props.location.pathname, {path: `${props.path}/sessions`, exact: true})
      }, {
        name: 'new_session_event',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('create_session_event', {}, 'cursus'),
        target: `${props.path}/events/form`,
        primary: true,
        displayed: !!matchPath(props.location.pathname, {path: `${props.path}/events`, exact: true})
      }, {
        name: 'new_cursus',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('create_cursus', {}, 'cursus'),
        target: `${props.path}/cursus/form`,
        primary: true,
        displayed: !!matchPath(props.location.pathname, {path: `${props.path}/cursus`, exact: true})
      }
    ]}
    subtitle={
      <Routes
        path={props.path}
        routes={[
          {
            path: '/courses',
            render: () => trans('courses', {}, 'cursus')
          }, {
            path: '/sessions',
            render: () => trans('sessions', {}, 'cursus')
          }, {
            path: '/events',
            render: () => trans('session_events', {}, 'cursus')
          }, {
            path: '/cursus',
            render: () => trans('cursus', {}, 'cursus')
          }, {
            path: '/queues',
            render: () => trans('pending_for_validation', {}, 'cursus')
          }, {
            path: '/parameters',
            render: () => trans('parameters')
          }
        ]}
      />
    }
  >
    <Routes
      path={props.path}
      redirect={[
        {from: '/', exact: true, to: '/courses'}
      ]}
      routes={[
        {
          path: '/courses',
          render: () => {
            const Courses = (
              <CourseTab path={props.path} />
            )

            return Courses
          }
        }, {
          path: '/sessions',
          render: () => {
            const Sessions = (
              <SessionTab path={props.path} />
            )

            return Sessions
          }
        }, {
          path: '/events',
          render: () => {
            const Events = (
              <SessionEventTab path={props.path} />
            )

            return Events
          }
        }, {
          path: '/cursus',
          render: () => {
            const Cursus = (
              <CursusTab path={props.path} />
            )

            return Cursus
          }
        }, {
          path: '/queues',
          component: Queues
        }, {
          path: '/parameters',
          render: () => {
            const ToolParameters = (
              <Parameters path={props.path} />
            )

            return ToolParameters
          },
          onEnter: () => props.openParametersForm(props.parameters),
          onLeave: () => props.openParametersForm(props.parameters)
        }
      ]}
    />
  </ToolPage>

CursusTool.propTypes = {
  location: T.object.isRequired,
  path: T.string.isRequired,
  parameters: T.shape(ParametersType.propTypes).isRequired,
  openParametersForm: T.func.isRequired
}

export {
  CursusTool
}