import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {RoutedPageContent} from '#/main/core/layout/router/components/page'
import {ResourcePage} from '#/main/core/resource/containers/page'
import {LINK_BUTTON, URL_BUTTON} from '#/main/app/buttons'

import {CustomDragLayer} from '#/plugin/exo/utils/custom-drag-layer'

import {Overview}   from '#/plugin/exo/quiz/overview/overview'
import {Player}     from '#/plugin/exo/quiz/player/components/player'
import {AttemptEnd} from '#/plugin/exo/quiz/player/components/attempt-end'
import {Editor}     from '#/plugin/exo/quiz/editor/components/editor'
import {Papers}     from '#/plugin/exo/quiz/papers/components/papers'
import {Paper}      from '#/plugin/exo/quiz/papers/components/paper'
import {Questions}  from '#/plugin/exo/quiz/correction/components/questions'
import {Answers}    from '#/plugin/exo/quiz/correction/components/answers'
import {Statistics} from '#/plugin/exo/quiz/statistics/components/statistics'

// todo : restore editor buttons

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
        label: trans('pass_quiz', {}, 'quiz'),
        target: '/play'
      }, {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-play',
        label: trans('exercise_try', {}, 'quiz'),
        displayed: props.editable,
        target: '/test'
      }, {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-list',
        label: trans('results_list', {}, 'quiz'),
        disabled: !props.hasPapers,
        displayed: props.registeredUser,
        target: '/papers',
        exact: true
      }, {
        type: URL_BUTTON,
        icon: 'fa fa-fw fa-table',
        label: trans('export_csv_results', {}, 'quiz'),
        disabled: !props.hasPapers,
        displayed: props.papersAdmin,
        target: ['exercise_papers_export', {exerciseId: props.quizId}]
      }, {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-check-square-o',
        label: trans('manual_correction', {}, 'quiz'),
        disabled: !props.hasPapers,
        displayed: props.papersAdmin,
        target: '/correction/questions'
      }, {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-bar-chart',
        label: trans('statistics', {}, 'quiz'),
        displayed: props.papersAdmin,
        target: '/statistics'
      }, {
        type: URL_BUTTON,
        icon: 'fa fa-fw fa-pie-chart',
        label: trans('docimology', {}, 'quiz'),
        displayed: props.docimologyAdmin,
        target: ['claro_resource_custom_action', {action: 'docimology', node: props.resourceNodeId}]
      }
    ]}
  >
    <RoutedPageContent
      key="resource-content"
      headerSpacer={true}
      routes={[
        {
          path: '/',
          exact: true,
          component: Overview,
          disabled: !props.hasOverview
        }, {
          path: '/edit',
          component: Editor,
          disabled: !props.editable,
          onEnter: () => {
            props.edit(props.quizId)
          }
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
          onEnter: (params) => props.loadCurrentPaper(params.id),
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
          disabled: !props.papersAdmin,
          onEnter: () => props.statistics()
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
  quizId: T.string.isRequired,
  resourceNodeId: T.string.isRequired,
  editable: T.bool.isRequired,
  papersAdmin: T.bool.isRequired,
  docimologyAdmin: T.bool.isRequired,
  hasPapers: T.bool.isRequired,
  registeredUser: T.bool.isRequired,
  hasOverview: T.bool.isRequired,
  edit: T.func.isRequired,
  testMode: T.func.isRequired,
  statistics: T.func.isRequired,
  correction: T.func.isRequired,
  loadCurrentPaper: T.func.isRequired,
  resetCurrentPaper: T.func.isRequired
}

export {
  QuizResource
}
