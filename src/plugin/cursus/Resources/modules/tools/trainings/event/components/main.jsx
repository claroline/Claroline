import React, {useCallback} from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {EventsList} from '#/plugin/cursus/tools/trainings/event/components/list'
import {EventShow} from '#/plugin/cursus/event/containers/show'
import {trans} from '#/main/app/intl'
import {constants} from '#/plugin/cursus/constants'
import {selectors} from '#/plugin/cursus/tools/trainings/event/store'
import {TrainingsEventUsers} from '#/plugin/cursus/tools/trainings/event/components/users'

const EventMain = (props) =>
  <Routes
    path={props.path+'/events'}
    routes={[
      {
        path: '/',
        exact: true,
        render: useCallback(() => (
          <EventsList
            path={props.contextPath}
            contextType={props.contextType}
            contextId={props.contextId}
            invalidateList={props.invalidateList}
            canEdit={props.canEdit}
          />
        ),[props.path, props.authenticated, props.canEdit, props.canRegister])
      }, {
        path: '/participants',
        render: useCallback(() => (
          <TrainingsEventUsers
            path={props.path}
            contextType={props.contextType}
            contextId={props.contextId}
            title={trans('participants')}
            type={constants.LEARNER_TYPE}
            name={selectors.STORE_NAME+'.participants'}
            canRegister={props.canRegister}
          />
        ), [props.path])
      }, {
        path: '/tutors',
        render: useCallback(() => (
          <TrainingsEventUsers
            path={props.path}
            contextType={props.contextType}
            contextId={props.contextId}
            title={trans('tutors', {}, 'cursus')}
            type={constants.TEACHER_TYPE}
            name={selectors.STORE_NAME+'.tutors'}
            canRegister={props.canRegister}
          />
        ), [props.path])
      }, {
        path: '/:id',
        render: useCallback((routerProps) => (
          <EventShow path={props.contextPath} id={routerProps.match.params.id} />
        ), [props.path])
      }
    ]}
  />

EventMain.propTypes = {
  path: T.string.isRequired,
  contextPath: T.string.isRequired,
  contextType: T.string.isRequired,
  contextId: T.string,
  authenticated: T.bool.isRequired,
  canEdit: T.bool.isRequired,
  canRegister: T.bool.isRequired,
  invalidateList: T.func.isRequired
}

export {
  EventMain
}