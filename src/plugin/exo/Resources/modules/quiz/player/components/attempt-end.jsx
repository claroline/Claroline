import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {LINK_BUTTON} from '#/main/app/buttons'
import {route} from '#/main/core/workspace/routing'
import {selectors as resourceSelect} from '#/main/core/resource/store'
import {selectors as securitySelectors} from '#/main/app/security/store/selectors'

import {ContentHtml} from '#/main/app/content/components/html'
import {ScoreGauge} from '#/main/core/layout/gauge/components/score'
import {ScoreBox} from '#/main/core/layout/evaluation/components/score-box'
import {calculateScore, calculateTotal} from '#/plugin/exo/items/score'
import {isQuestionType} from '#/plugin/exo/items/item-types'

import {select as playerSelect} from '#/plugin/exo/quiz/player/selectors'
import {showCorrection, showScore} from '#/plugin/exo/resources/quiz/papers/restrictions'
import {AttemptsChart} from '#/plugin/exo/charts/attempts/components/chart'

// TODO : merge with PlayerRestrictions
// TODO : show number of attempts info

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
  <div className="quiz-player">
    <div className="row">
      {props.showAttemptScore &&
        <div className="col-md-3 text-center" style={{marginTop: '20px'}}>
          <ScoreGauge
            type="user"
            value={get(props.paper, 'score')}
            total={get(props.paper, 'total')}
            width={140}
            height={140}
            displayValue={value => undefined === value || null === value ? '?' : value+''}
          />
        </div>
      }

      <div className={props.showAttemptScore ? 'col-md-9':'col-md-12'}>
        {props.showAttemptScore &&
        get(props.paper, 'total') &&
        get(props.paper, 'structure.parameters.successScore') &&
        get(props.paper, 'structure.parameters.successMessage') &&
        (get(props.paper, 'score') / get(props.paper, 'total')) * 100 >= get(props.paper, 'structure.parameters.successScore') &&
          <div className="alert alert-info">
            <ContentHtml>{get(props.paper, 'structure.parameters.successMessage')}</ContentHtml>
          </div>
        }

        {props.showAttemptScore &&
        get(props.paper, 'total') &&
        get(props.paper, 'structure.parameters.successScore') &&
        get(props.paper, 'structure.parameters.failureMessage') &&
        (get(props.paper, 'score') / get(props.paper, 'total')) * 100 < get(props.paper, 'structure.parameters.successScore') &&
          <div className="alert alert-danger">
            <ContentHtml>{get(props.paper, 'structure.parameters.failureMessage')}</ContentHtml>
          </div>
        }

        {props.endMessage ?
          <ContentHtml className="component-container">{props.endMessage}</ContentHtml> :
          <Fragment>
            <h2 className="h3">{trans('attempt_end_title', {}, 'quiz')}</h2>
            <p>{trans('attempt_end_info', {}, 'quiz')}</p>
          </Fragment>
        }

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

        {props.endNavigation &&
          <Toolbar
            buttonName="btn btn-block btn-emphasis"
            toolbar="test restart correction statistics home"
            actions={[
              {
                name: 'test',
                type: LINK_BUTTON,
                icon: 'fa fa-fw fa-flask',
                label: trans('test', {}, 'actions'),
                target: `${props.path}/test`,
                exact: true,
                primary: true,
                displayed: props.testMode
              }, {
                name: 'restart',
                type: LINK_BUTTON,
                icon: 'fa fa-fw fa-redo',
                label: trans('exercise_restart', {}, 'quiz'),
                target: `${props.path}/play`,
                exact: true,
                primary: true,
                displayed: props.hasMoreAttempts
              }, {
                name: 'correction',
                type: LINK_BUTTON,
                icon: 'fa fa-fw fa-check-double',
                label: trans('view_paper', {}, 'quiz'),
                target: `${props.path}/papers/${props.paper.id}`,
                displayed: props.showAttemptCorrection,
                primary: true
              }, {
                name: 'statistics',
                type: LINK_BUTTON,
                icon: 'fa fa-fw fa-bar-chart',
                label: trans('statistics', {}, 'quiz'),
                target: `${props.path}/statistics`,
                displayed: props.showStatistics
              }, {
                name: 'home',
                type: LINK_BUTTON,
                icon: 'fa fa-fw fa-home',
                label: trans('return-home', {}, 'actions'),
                target: route(props.workspace),
                displayed: !!props.workspace,
                exact: true
              }
            ]}
          />
        }
      </div>
    </div>
  </div>

AttemptEndComponent.propTypes = {
  path: T.string.isRequired,
  workspace: T.object,
  currentUserId: T.string.isRequired,
  paper: T.shape({ // TODO : paper prop types
    id: T.string.isRequired,
    structure: T.object.isRequired,
    finished: T.bool.isRequired
  }).isRequired,
  tags: T.array,
  answers: T.object,
  testMode: T.bool.isRequired,
  hasMoreAttempts: T.bool.isRequired,
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
      workspace: resourceSelect.workspace(state),
      currentUserId: securitySelectors.currentUserId(state),
      paper: paper,
      answers: playerSelect.answers(state),
      tags: playerSelect.tags(state),
      testMode: playerSelect.testMode(state),
      hasMoreAttempts: playerSelect.hasMoreAttempts(state),
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
