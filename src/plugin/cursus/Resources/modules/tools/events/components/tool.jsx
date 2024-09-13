import React from 'react'
import get from 'lodash/get'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {Tool} from '#/main/core/tool'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Course} from '#/plugin/cursus/course/containers/main'
import {Course as CourseTypes} from '#/plugin/cursus/prop-types'
import {CourseCreation} from '#/plugin/cursus/course/components/creation'
import {CourseEditor} from '#/plugin/cursus/course/editor/containers/main'

import {EmptyCourse} from '#/plugin/cursus/course/components/empty'
import {EventsAll} from '#/plugin/cursus/tools/events/components/all'
import {EventsPublic} from '#/plugin/cursus/tools/events/components/public'
import {EventsDetails} from '#/plugin/cursus/tools/events/containers/details'
import {EventsPresences} from '#/plugin/cursus/tools/events/containers/presences'
import {EventsRegistered} from '#/plugin/cursus/tools/events/components/registered'

const EventsTool = (props) =>
  <Tool
    {...props}
    redirect={[
      {from: '/', exact: true, to: props.course ? '/course/' + props.course.slug : '/course'}
    ]}
    menu={[
      {
        name: 'about',
        type: LINK_BUTTON,
        label: trans('about', {}, 'platform'),
        target: props.course ? props.path + '/course/' + props.course.slug : props.path + '/course'
      }, {
        name: 'registered',
        type: LINK_BUTTON,
        label: trans('my_events', {}, 'cursus'),
        target: props.path + '/registered'
      }, {
        name: 'public',
        type: LINK_BUTTON,
        label: trans('public_events', {}, 'cursus'),
        target: props.path + '/public'
      }, {
        name: 'all',
        type: LINK_BUTTON,
        label: trans('all_events', {}, 'cursus'),
        target: props.path + '/all',
        displayed: props.canEdit || props.canRegister
      }, {
        name: 'presences',
        type: LINK_BUTTON,
        label: (props.canEdit || props.canRegister) ? trans('presences', {}, 'cursus') : trans('my_presences', {}, 'cursus'),
        target: props.path + '/presences'
      }
    ]}
    pages={[
      {
        path: '/new',
        onEnter: () => props.openForm(null, CourseTypes.defaultProps, props.currentContext.data),
        disabled: !props.canEdit,
        component: CourseCreation
      }, {
        path: '/course/:courseSlug/edit',
        onEnter: () => props.openForm(props.course.slug),
        component: CourseEditor
      }, {
        path: '/course',
        onEnter: () => {
          if (props.course) {
            return props.openCourse(props.course.slug)
          }
        },
        render: (params = {}) => {
          if (props.course) {
            return (
              <Course
                path={props.path + '/course/' + props.course.slug}
                slug={props.course.slug}
                history={params.history}
              />)
          } else {
            return (
              <EmptyCourse
                path={props.path}
                canEdit={props.canEdit}
                contextType={props.contextType}
                contextId={props.currentContext.data}
                openForm={props.openForm}
              />
            )
          }
        }
      }, {
        path: '/registered',
        onEnter: props.invalidateList,
        render: () => (
          <EventsRegistered
            path={props.path}
            contextId={get(props.currentContext, 'data.id')}
            invalidateList={props.invalidateList}
          />
        )
      }, {
        path: '/public',
        onEnter: props.invalidateList,
        render: () => (
          <EventsPublic
            path={props.path}
            contextId={get(props.currentContext, 'data.id')}
          />
        )
      }, {
        path: '/all',
        onEnter: props.invalidateList,
        disabled: !props.canEdit && !props.canRegister,
        render: () => (
          <EventsAll
            path={props.path}
            contextId={get(props.currentContext, 'data.id')}
          />
        )
      }, {
        path: '/presences',
        component: EventsPresences
      }, {
        path: '/:id',
        onEnter: (params = {}) => props.open(params.id),
        component: EventsDetails
      }
    ]}
  />

EventsTool.propTypes = {
  path: T.string.isRequired,
  currentContext: T.shape({
    type: T.string,
    data: T.object
  }).isRequired,
  contextType: T.string,
  canEdit: T.bool.isRequired,
  canRegister: T.bool.isRequired,
  invalidateList: T.func.isRequired,
  open: T.func.isRequired,
  openForm: T.func.isRequired,
  openCourse: T.func,
  course: T.shape({
    slug: T.string
  })
}

export {
  EventsTool
}
