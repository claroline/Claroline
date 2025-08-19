import React, {useCallback} from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl'
import {Routes} from '#/main/app/router/components/routes'
import {selectors as toolSelectors} from '#/main/core/tool'

import {TrainingsSessionList} from '#/plugin/cursus/tools/trainings/session/components/list'
import {SessionShow} from '#/plugin/cursus/session/containers/show'
import {TrainingsSessionUsers} from '#/plugin/cursus/tools/trainings/session/components/users'
import {constants} from '#/plugin/cursus/constants'
import {selectors as trainingSelectors} from '#/plugin/cursus/tools/trainings/store'
import {selectors} from '#/plugin/cursus/tools/trainings/session/store'

const SessionMain = (props) => {
  const loaded = useSelector(toolSelectors.loaded)
  const contextType = useSelector(toolSelectors.contextType)
  const contextId = useSelector(toolSelectors.contextId)
  const canRegister = useSelector(state => toolSelectors.hasPermission('follow', state))
  const canCreateSession = useSelector(state => toolSelectors.hasPermission('edit', state))
  const course = useSelector(trainingSelectors.course)

  return (
    <Routes
      path={`${props.path}/sessions`}
      routes={[
        {
          path: '/',
          exact: true,
          render: useCallback(() => (
            <TrainingsSessionList
              path={props.path}
              course={course}
              contextType={contextType}
              contextId={contextId}
              invalidateList={props.invalidateList}
              canCreateSession={canCreateSession}
            />
          ), [props.path, loaded])
        }, {
          path: '/participants',
          render: useCallback(() => (
            <TrainingsSessionUsers
              path={props.path}
              contextType={contextType}
              contextId={contextId}
              title={trans('participants')}
              type={constants.LEARNER_TYPE}
              name={selectors.STORE_NAME+'.participants'}
              canRegister={canRegister}
            />
          ), [props.path, loaded])
        }, {
          path: '/tutors',
          render: useCallback(() => (
            <TrainingsSessionUsers
              path={props.path}
              contextType={contextType}
              contextId={contextId}
              title={trans('tutors', {}, 'cursus')}
              type={constants.TEACHER_TYPE}
              name={selectors.STORE_NAME+'.tutors'}
              canRegister={canRegister}
            />
          ), [props.path, loaded])
        }, {
          path: '/:id',
          render: useCallback((routerProps) => (
            <SessionShow path={props.path} id={routerProps.match.params.id} />
          ), [props.path, loaded])
        }
      ]}
    />
  )
}

SessionMain.propTypes = {
  path: T.string.isRequired,
  invalidateList: T.func.isRequired
}

export {
  SessionMain
}
