import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Resource} from '#/main/core/resource'

import {QuizPlayer} from '#/plugin/exo/resources/quiz/player/containers/main'
import {AttemptEnd} from '#/plugin/exo/resources/quiz/player/components/attempt-end'

import {QuizOverview} from '#/plugin/exo/resources/quiz/containers/overview'
import {QuizEditor} from '#/plugin/exo/resources/quiz/editor/components/main'
import {PapersMain} from '#/plugin/exo/resources/quiz/papers/containers/main'
import {CorrectionMain} from '#/plugin/exo/resources/quiz/correction/containers/main'
import {QuizDashboard} from '#/plugin/exo/resources/quiz/dashboard/components/main'

const QuizResource = props =>
  <Resource
    {...omit(props)}
    styles={['claroline-distribution-plugin-exo-quiz-resource']}
    actions={[
      {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-play',
        label: trans('start', {}, 'actions'),
        disabled: props.empty,
        displayed: props.editable,
        target: `${props.path}/play`,
        exact: true
      }, {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-flask',
        label: trans('test', {}, 'actions'),
        displayed: props.editable,
        disabled: props.empty,
        target: `${props.path}/test`,
        group: trans('management'),
        exact: true
      }, {
        name: 'results',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-tasks',
        label: trans('show-results', {}, 'actions'),
        displayed: props.registeredUser,
        target: `${props.path}/papers`,
        exact: true
      }, {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-check-square',
        label: trans('correct', {}, 'actions'),
        displayed: props.canFollow,
        target: `${props.path}/correction`,
        group: trans('management')
      }
    ]}

    overviewPage={props.hasOverview ? QuizOverview : undefined}
    editor={QuizEditor}
    dashboard={QuizDashboard}
    pages={[
      {
        path: '/test',
        component: QuizPlayer,
        disabled: !props.editable,
        onEnter: () => props.testMode(true)
      }, {
        path: '/play',
        exact: true,
        component: QuizPlayer,
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
        disabled: !props.canFollow
      }
    ]}
    redirect={[
      {from: '/', exact: true, to: '/play', disabled: props.hasOverview || props.editable},
      {from: '/', exact: true, to: '/test', disabled: props.hasOverview || !props.editable}
    ]}
  />

QuizResource.propTypes = {
  path: T.string.isRequired,
  quizId: T.string,
  empty: T.bool.isRequired,
  editable: T.bool.isRequired,
  canFollow: T.bool.isRequired,
  registeredUser: T.bool.isRequired,
  hasOverview: T.bool.isRequired,
  testMode: T.func.isRequired
}

export {
  QuizResource
}
