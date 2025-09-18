import React from 'react'
import {useDispatch, useSelector} from 'react-redux'

import {trans} from '#/main/app/intl'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Nav} from '#/main/app/components/nav'
import {Routes} from '#/main/app/router'
import {ResourceDashboardStats, selectors} from '#/main/core/resource/dashboard'

import {AnswersStats} from '#/plugin/exo/resources/quiz/statistics/containers/answers'
import {AttemptsStats} from '#/plugin/exo/resources/quiz/statistics/containers/attempts'
import {Docimology} from '#/plugin/exo/resources/quiz/statistics/containers/docimology'
import {actions} from '#/plugin/exo/resources/quiz/statistics/store'
import {selectors as quizSelectors} from '#/plugin/exo/resources/quiz/store'


const QuizDashboardStats = () => {
  const dispatch = useDispatch()

  const quizId = useSelector(quizSelectors.id)
  const dashboardPath = useSelector(selectors.path)

  return (
    <ResourceDashboardStats>
      <Nav
        className="nav-justified content-lg mb-4 px-4"
        orientation="horizontal"
        variant="bar"
        items={[
          {
            name: 'answers',
            type: LINK_BUTTON,
            label: trans('Réponses des utilisateurs'),
            target: `${dashboardPath}/stats/answers`,
            exact: true
          }, {
            name: 'attempts',
            type: LINK_BUTTON,
            label: trans('Evolution des tentatives', {}, 'quiz'),
            target: `${dashboardPath}/stats/attempts`
          }, {
            name: 'docimology',
            type: LINK_BUTTON,
            label: trans('Docimologie', {}, 'quiz'),
            target: `${dashboardPath}/stats/docimology`
          }
        ]}
      />

      <Routes
        path={`${dashboardPath}/stats`}
        redirect={[
          {from: '/', exact: true, to: '/answers'}
        ]}
        routes={[
          {
            path: '/answers',
            component: AnswersStats,
            onEnter: () => dispatch(actions.fetchStatistics(quizId))
          }, {
            path: '/attempts',
            component: AttemptsStats
          }, {
            path: '/docimology',
            component: Docimology,
            onEnter: () => dispatch(actions.fetchDocimology(quizId))
          }
        ]}
      />
    </ResourceDashboardStats>
  )
}

export {
  QuizDashboardStats
}
