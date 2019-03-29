import React from 'react'
import {PropTypes as T} from 'prop-types'

import {currentUser} from '#/main/app/security'
import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {ResourcePage} from '#/main/core/resource/containers/page'
import {LINK_BUTTON, URL_BUTTON} from '#/main/app/buttons'

import {CustomDragLayer} from '#/plugin/exo/utils/custom-drag-layer'

import {Overview}   from '#/plugin/exo/quiz/overview/overview'
import {Player}     from '#/plugin/exo/quiz/player/components/player'
import {AttemptEnd} from '#/plugin/exo/quiz/player/components/attempt-end'
import {Papers}     from '#/plugin/exo/quiz/papers/components/papers'
import {Paper}      from '#/plugin/exo/quiz/papers/components/paper'
import {Questions}  from '#/plugin/exo/quiz/correction/components/questions'
import {Answers}    from '#/plugin/exo/quiz/correction/components/answers'
import {Statistics} from '#/plugin/exo/quiz/statistics/components/statistics'

import {EditorMain} from '#/plugin/exo/resources/quiz/editor/containers/main'

const authenticatedUser = currentUser()

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
        target: '/play'
      }, {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-play',
        label: trans('test', {}, 'actions'),
        displayed: props.editable,
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
        target: '/correction/questions',
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
          component: Overview,
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
          exact: true,
          component: Papers,
          disabled: !props.registeredUser
        }, {
          path: '/papers/:id', // todo : declare inside papers module
          component: Paper,
          onEnter: (params) => {
            authenticatedUser ? props.loadCurrentPaper(props.quizId, params.id) : false

            if (props.showStatistics) {
              props.statistics(props.quizId)
            }
          },
          onLeave: () => props.resetCurrentPaper()
        }, {
          path: '/correction/questions',
          exact: true,
          component: Questions,
          disabled: !props.papersAdmin,
          onEnter: () => props.correction()
        }, {
          path: '/correction/questions/:id', // todo : declare inside correction module
          component: Answers,
          disabled: !props.papersAdmin,
          onEnter: (params = {}) => props.correction(params.id)
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
  editable: T.bool.isRequired,
  papersAdmin: T.bool.isRequired,
  docimologyAdmin: T.bool.isRequired,
  showStatistics: T.bool.isRequired,
  registeredUser: T.bool.isRequired,
  hasOverview: T.bool.isRequired,
  testMode: T.func.isRequired,
  statistics: T.func.isRequired,
  correction: T.func.isRequired,
  loadCurrentPaper: T.func.isRequired,
  resetCurrentPaper: T.func.isRequired
}

export {
  QuizResource
}
