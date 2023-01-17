import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import classes from 'classnames'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {LINK_BUTTON, URL_BUTTON} from '#/main/app/buttons'
import {route as desktopRoute} from '#/main/core/tool/routing'
import {route as workspaceRoute} from '#/main/core/workspace/routing'
import {route as resourceRoute} from '#/main/core/resource/routing'
import {selectors as securitySelectors} from '#/main/app/security/store/selectors'
import {selectors as resourceSelect} from '#/main/core/resource/store'
import {ResourceEnd} from '#/main/core/resource/components/end'

import {ScoreBox} from '#/main/core/layout/evaluation/components/score-box'
import {calculateScore, calculateTotal} from '#/plugin/exo/items/score'
import {isQuestionType} from '#/plugin/exo/items/item-types'

import {select as playerSelect} from '#/plugin/exo/quiz/player/selectors'
import {showCorrection, showScore} from '#/plugin/exo/resources/quiz/papers/restrictions'
import {AttemptsChart} from '#/plugin/exo/charts/attempts/components/chart'

const IntermediateScores = (props) => {
  let intermediate = []
  if ('tag' === props.mode) {
    const all = [].concat(...props.steps.map(step => step.items || []))
    intermediate = props.tags.map(tag => {
      let score = 0
      let total = 0
      all.map(item => {
        if (isQuestionType(item.type) && item.tags && -1 !== item.tags.indexOf(tag)) {
          if (props.answers[item.id]) {
            score += calculateScore(item, props.answers[item.id]) // this should retrieve value from api instead
          }

          total += calculateTotal(item)
        }
      })

      if (props.total) {
        score = (score * props.total) / total
      }

      return {
        title: tag,
        score: score,
        total: props.total ? props.total : total
      }
    })
  } else if ('step' === props.mode) {
    intermediate = props.steps.map((step, stepIndex) => {
      let score = 0
      let total = 0
      step.items.map(item => {
        if (isQuestionType(item.type)) {
          if (props.answers[item.id]) {
            score += calculateScore(item, props.answers[item.id]) // this should retrieve value from api instead
          }

          total += calculateTotal(item)
        }
      })

      if (props.total) {
        score = (score * props.total) / total
      }

      return {
        title: step.title || trans('step', {number: stepIndex + 1}, 'quiz'), // this should also show numbering
        score: score,
        total: props.total ? props.total : total
      }
    })
  }

  if (0 !== intermediate) {
    return (
      <ul className="list-group list-group-values">
        {intermediate.map((intermediateScore, i) => (
          <li key={i} className="list-group-item">
            {intermediateScore.title}

            <span className="value">
              <ScoreBox className="pull-right" score={intermediateScore.score} scoreMax={intermediateScore.total}/>
            </span>
          </li>
        ))}
      </ul>
    )
  }

  return null
}

IntermediateScores.propTypes = {
  mode: T.string,
  steps: T.array,
  tags: T.array,
  total: T.number,
  answers: T.object
}

IntermediateScores.defaultProps = {
  mode: 'none',
  answers: {},
  steps: []
}

const AttemptEndComponent = props =>
  <ResourceEnd
    contentText={props.endMessage ||
      <Fragment>
        <h2 className="h3">{trans('attempt_end_title', {}, 'quiz')}</h2>
        <p>{trans('attempt_end_info', {}, 'quiz')}</p>
      </Fragment>
    }
    attempt={props.attempt}
    workspace={props.workspace}
    display={{
      score: props.showAttemptScore,
      scoreMax: get(props.paper, 'total'),
      successScore: get(props.paper, 'structure.parameters.successScore'),
      feedback: !!get(props.paper, 'structure.parameters.successMessage') || !!get(props.paper, 'structure.parameters.failureMessage'),
      toolbar: props.endNavigation,
      certificates: get(props.paper, 'structure.parameters.workspaceCertificates')
    }}
    feedbacks={{
      success: get(props.paper, 'structure.parameters.successMessage'),
      failure: get(props.paper, 'structure.parameters.failureMessage'),
      closed: !props.hasMoreAttempts ? [[
        trans('exercise_attempt_limit', {}, 'quiz'),
        get(props.paper, 'structure.parameters.attemptsReachedMessage')
      ]] : undefined
    }}
    details={[
      [trans('attempts'), get(props.userEvaluation, 'nbAttempts', 0) + (props.maxAttempts ? ' / ' + props.maxAttempts : '') ]
    ]}

    actions={[
      {
        name: 'restart',
        type: LINK_BUTTON,
        label: trans('exercise_restart', {}, 'quiz'),
        target: `${props.path}/play`,
        exact: true,
        primary: true,
        displayed: props.hasMoreAttempts
      }, {
        name: 'test',
        type: LINK_BUTTON,
        label: trans('test', {}, 'actions'),
        target: `${props.path}/test`,
        exact: true,
        displayed: props.testMode
      }, {
        name: 'correction',
        type: LINK_BUTTON,
        label: trans('view_paper', {}, 'quiz'),
        target: `${props.path}/papers/${props.paper.id}`,
        displayed: props.showAttemptCorrection
      }, {
        name: 'statistics',
        type: LINK_BUTTON,
        label: trans('statistics', {}, 'quiz'),
        target: `${props.path}/statistics`,
        displayed: props.showStatistics
      }
    ].concat(get(props.paper, 'structure.parameters.back.type') ? [
      {
        name: 'home',
        type: URL_BUTTON, // we require an URL_BUTTON here to escape the embedded resource router
        label: get(props.paper, 'structure.parameters.back.label') || trans('return-home', {}, 'actions'),
        target: '#'+classes({
          [desktopRoute()]: 'desktop' === get(props.paper, 'structure.parameters.back.type'),
          [props.workspace ? workspaceRoute(props.workspace) : undefined]: 'workspace' === get(props.paper, 'structure.parameters.back.type'),
          [get(props.paper, 'structure.parameters.back.target') ? resourceRoute(get(props.paper, 'structure.parameters.back.target')) : undefined]: 'resource' === get(props.paper, 'structure.parameters.back.type')
        })
      }
    ] : [])}
  >
    {props.showAttemptScore &&
      <IntermediateScores
        mode={get(props.paper, 'structure.parameters.intermediateScores')}
        steps={props.paper.structure.steps}
        tags={props.tags}
        answers={props.answers}
        total={get(props.paper, 'structure.score.total')}
      />
    }

    {props.showEndStats && ['user', 'both'].includes(get(props.paper, 'structure.parameters.overviewStats')) &&
      <AttemptsChart
        quizId={props.paper.structure.id}
        userId={props.currentUserId}
        steps={props.paper.structure.steps}
        questionNumberingType={get(props.paper, 'structure.parameters.questionNumbering')}
      />
    }

    {props.showEndStats && ['all', 'both'].includes(get(props.paper, 'structure.parameters.overviewStats')) &&
      <AttemptsChart
        quizId={props.paper.structure.id}
        steps={props.paper.structure.steps}
        questionNumberingType={get(props.paper, 'structure.parameters.questionNumbering')}
      />
    }
  </ResourceEnd>

AttemptEndComponent.propTypes = {
  path: T.string.isRequired,
  workspace: T.object,
  currentUserId: T.string.isRequired,
  userEvaluation: T.object,
  attempt: T.object,
  paper: T.shape({ // TODO : paper prop types
    id: T.string.isRequired,
    structure: T.object.isRequired,
    finished: T.bool.isRequired
  }).isRequired,
  tags: T.array,
  answers: T.object,
  testMode: T.bool.isRequired,
  maxAttempts: T.number,
  hasMoreAttempts: T.bool.isRequired,
  attemptsReachedMessage: T.string,
  endMessage: T.string,
  endNavigation: T.bool.isRequired,
  showStatistics: T.bool.isRequired,
  showAttemptScore: T.bool.isRequired,
  showAttemptCorrection: T.bool.isRequired,
  showEndStats: T.bool.isRequired
}

const AttemptEnd = connect(
  (state) => {
    const isAdmin = hasPermission('edit', resourceSelect.resourceNode(state)) || hasPermission('manage_papers', resourceSelect.resourceNode(state))
    const paper = playerSelect.paper(state)

    return {
      path: resourceSelect.path(state),
      userEvaluation: resourceSelect.resourceEvaluation(state),
      workspace: resourceSelect.workspace(state),
      currentUserId: securitySelectors.currentUserId(state),
      paper: paper,
      attempt: playerSelect.attempt(state),
      answers: playerSelect.answers(state),
      tags: playerSelect.tags(state),
      testMode: playerSelect.testMode(state),
      hasMoreAttempts: playerSelect.hasMoreAttempts(state),
      maxAttempts: playerSelect.maxAttempts(state),
      attemptsReachedMessage: playerSelect.attemptsReachedMessage(state),
      endMessage: playerSelect.quizEndMessage(state),
      endNavigation: playerSelect.quizEndNavigation(state),

      showAttemptScore: showScore(paper, isAdmin),
      showAttemptCorrection: showCorrection(paper, isAdmin),
      showStatistics: playerSelect.showStatistics(state),
      showEndStats: playerSelect.showEndStats(state)
    }
  }
)(AttemptEndComponent)

export {
  AttemptEnd
}
