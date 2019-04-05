import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {LINK_BUTTON, URL_BUTTON} from '#/main/app/buttons'

import {HtmlText} from '#/main/core/layout/components/html-text'
import {ScoreGauge} from '#/main/core/layout/evaluation/components/score-gauge'
import {selectors as resourceSelect} from '#/main/core/resource/store'

import {select as playerSelect} from '#/plugin/exo/quiz/player/selectors'
import {selectors as playerSelectors} from '#/plugin/exo/resources/quiz/player/store'
import quizSelectors from '#/plugin/exo/quiz/selectors'
import {selectors as paperSelectors} from '#/plugin/exo/quiz/papers/selectors'
import {utils as paperUtils} from '#/plugin/exo/quiz/papers/utils'

// TODO : merge with PlayerRestrictions

const AttemptEndComponent = props => {
  const showScore = paperUtils.showScore(false, props.paper.finished, paperSelectors.showScoreAt(props.paper), paperSelectors.showCorrectionAt(props.paper), paperSelectors.correctionDate(props.paper))
  const showCorrection = paperUtils.showCorrection(false, props.paper.finished, paperSelectors.showCorrectionAt(props.paper), paperSelectors.correctionDate(props.paper))
  const answers = Object.keys(props.answers).map(key => props.answers[key])
  const hasMoreAttempts = (0 === props.maxAttempts || props.maxAttempts > props.userPaperCount) &&
    (0 === props.maxAttemptsPerDay || props.maxAttemptsPerDay > props.userPaperDayCount)

  return (
    <div className="quiz-player">
      <div className="row">
        {showScore &&
          <div className="col-md-3 text-center">
            <ScoreGauge
              userScore={paperUtils.computeScore(props.paper, answers)}
              maxScore={paperSelectors.paperScoreMax(props.paper)}
            />
          </div>
        }

        <div className={showScore ? 'col-md-9':'col-md-12'}>
          {props.endMessage ?
            <HtmlText>{props.endMessage}</HtmlText> :
            <div>
              <h2 className="h4">{trans('attempt_end_title', {}, 'quiz')}</h2>
              <p>{trans('attempt_end_info', {}, 'quiz')}</p>
            </div>
          }

          {props.endNavigation &&
            <Toolbar
              buttonName="btn btn-block btn-emphasis"
              toolbar="restart correction statistics home"
              actions={[
                {
                  name: 'restart',
                  type: LINK_BUTTON,
                  icon: 'fa fa-fw fa-redo',
                  label: trans('exercise_restart', {}, 'quiz'),
                  target: '/play',
                  exact: true,
                  primary: true,
                  displayed: hasMoreAttempts
                }, {
                  name: 'correction',
                  type: LINK_BUTTON,
                  icon: 'fa fa-fw fa-check-double',
                  label: trans('view_paper', {}, 'quiz'),
                  target: `/papers/${props.paper.id}`,
                  displayed: showCorrection,
                  primary: true
                }, {
                  name: 'statistics',
                  type: LINK_BUTTON,
                  icon: 'fa fa-fw fa-bar-chart',
                  label: trans('statistics', {}, 'quiz'),
                  target: '/statistics',
                  displayed: props.showStatistics
                }, {
                  name: 'home',
                  type: URL_BUTTON,
                  icon: 'fa fa-fw fa-home',
                  label: trans('return-home', {}, 'actions'),
                  target: ['claro_workspace_open', {workspaceId: props.workspaceId}],
                  displayed: !!props.workspaceId
                }
              ]}
            />
          }
        </div>
      </div>
    </div>
  )
}

AttemptEndComponent.propTypes = {
  workspaceId: T.number,
  admin: T.bool.isRequired,
  answers: T.object.isRequired,
  paper: T.shape({
    id: T.string.isRequired,
    structure: T.object.isRequired,
    finished: T.bool.isRequired
  }).isRequired,
  endMessage: T.string,
  endNavigation: T.bool.isRequired,
  showStatistics: T.bool.isRequired,
  maxAttempts: T.number.isRequired,
  maxAttemptsPerDay: T.number.isRequired,
  userPaperCount: T.number.isRequired,
  userPaperDayCount: T.number.isRequired
}

const AttemptEnd = connect(
  (state) => ({
    workspaceId: resourceSelect.workspaceId(state),
    admin: hasPermission('edit', resourceSelect.resourceNode(state)) || quizSelectors.papersAdmin(state),
    paper: playerSelect.paper(state),
    endMessage: playerSelect.quizEndMessage(state),
    endNavigation: playerSelect.quizEndNavigation(state),
    answers: playerSelect.answers(state),
    showStatistics: quizSelectors.parameters(state).showStatistics,
    maxAttempts: quizSelectors.parameters(state).maxAttempts,
    maxAttemptsPerDay: quizSelectors.parameters(state).maxAttemptsPerDay,
    userPaperCount: playerSelectors.userPaperCount(state),
    userPaperDayCount: playerSelectors.userPaperDayCount(state)
  })
)(AttemptEndComponent)

export {
  AttemptEnd
}
