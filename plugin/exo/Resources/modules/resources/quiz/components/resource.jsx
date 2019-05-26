import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {ResourcePage} from '#/main/core/resource/containers/page'
import {LINK_BUTTON, URL_BUTTON} from '#/main/app/buttons'

import {CustomDragLayer} from '#/plugin/exo/utils/custom-drag-layer'

import {Player}     from '#/plugin/exo/quiz/player/components/player'
import {AttemptEnd} from '#/plugin/exo/quiz/player/components/attempt-end'
import {Statistics} from '#/plugin/exo/quiz/statistics/components/statistics'

import {EditorMain}   from '#/plugin/exo/resources/quiz/editor/containers/main'
import {OverviewMain} from '#/plugin/exo/resources/quiz/overview/containers/main'
import {PapersMain}   from '#/plugin/exo/resources/quiz/papers/containers/main'
import {CorrectionMain}   from '#/plugin/exo/resources/quiz/correction/containers/main'

const QuizResource = props =>
  <ResourcePage
    styles={['claroline-distribution-plugin-exo-quiz-resource']}
    customActions={[
      {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-home',
        label: trans('show_overview'),
        displayed: props.hasOverview,
        target: '/',
        exact: true
      }, {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-play',
        label: trans('start', {}, 'actions'),
        disabled: props.empty,
        target: '/play'
      }, {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-flask',
        label: trans('test', {}, 'actions'),
        displayed: props.editable,
        disabled: props.empty,
        target: '/test',
        group: trans('management')
      }, {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-tasks',
        label: trans('show-results', {}, 'actions'),
        displayed: props.registeredUser,
        target: '/papers',
        exact: true
      }, {
        type: URL_BUTTON,
        icon: 'fa fa-fw fa-file-csv',
        label: trans('export_csv_results', {}, 'quiz'),
        displayed: props.papersAdmin,
        target: ['exercise_papers_export', {exerciseId: props.quizId}],
        group: trans('transfer')
      }, {
        type: URL_BUTTON,
        icon: 'fa fa-fw fa-file-csv',
        label: trans('export_csv_answers', {}, 'quiz'),
        displayed: props.papersAdmin,
        target: ['exercise_papers_export_csv', {exerciseId: props.quizId}],
        group: trans('transfer')
      }, {
        type: URL_BUTTON,
        icon: 'fa fa-fw fa-file-code',
        label: trans('export_json_answers', {}, 'quiz'),
        displayed: props.papersAdmin,
        target: ['exercise_papers_export_json', {exerciseId: props.quizId}],
        group: trans('transfer')
      }, {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-check-square-o',
        label: trans('correct', {}, 'actions'),
        displayed: props.papersAdmin,
        target: '/correction',
        group: trans('management')
      }, {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-bar-chart',
        label: trans('show-statistics', {}, 'actions'),
        displayed: props.papersAdmin,
        target: '/statistics'
      }, {
        type: URL_BUTTON,
        icon: 'fa fa-fw fa-pie-chart',
        label: trans('view_docimology', {}, 'actions'),
        displayed: props.docimologyAdmin,
        target: ['exercise_docimology', {id: props.quizId}]
      }
    ]}
  >
    <Routes
      key="resource-content"
      routes={[
        {
          path: '/',
          exact: true,
          component: OverviewMain,
          disabled: !props.hasOverview
        }, {
          path: '/edit',
          component: EditorMain,
          disabled: !props.editable
        }, {
          path: '/test',
          component: Player,
          disabled: !props.editable,
          onEnter: () => props.testMode(true)
        }, {
          path: '/play',
          exact: true,
          component: Player,
          onEnter: () => props.testMode(false)
        }, {
          path: '/play/end', // todo : declare inside player module
          component: AttemptEnd
        }, {
          path: '/papers',
          component: PapersMain
        }, {
          path: '/correction',
          component: CorrectionMain,
          disabled: !props.papersAdmin
        }, {
          path: '/statistics',
          component: Statistics,
          disabled: !props.papersAdmin && !props.showStatistics,
          onEnter: () => props.statistics(props.quizId)
        }
      ]}
      redirect={[
        {
          from: '/',
          exact: true,
          to: '/play',
          disabled: props.hasOverview || props.editable
        }, {
          from: '/',
          exact: true,
          to: '/test',
          disabled: props.hasOverview || !props.editable
        }
      ]}
    />

    <CustomDragLayer key="drag-layer" />
  </ResourcePage>

QuizResource.propTypes = {
  quizId: T.string,
  empty: T.bool.isRequired,
  editable: T.bool.isRequired,
  papersAdmin: T.bool.isRequired,
  docimologyAdmin: T.bool.isRequired,
  showStatistics: T.bool.isRequired,
  registeredUser: T.bool.isRequired,
  hasOverview: T.bool.isRequired,
  testMode: T.func.isRequired,
  statistics: T.func.isRequired
}

export {
  QuizResource
}
