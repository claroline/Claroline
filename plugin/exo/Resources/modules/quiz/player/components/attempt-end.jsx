import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans, tex} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {LINK_BUTTON, URL_BUTTON} from '#/main/app/buttons'
import {HtmlText} from '#/main/core/layout/components/html-text'
import {selectors as resourceSelect} from '#/main/core/resource/store'

import {select as playerSelectors} from './../selectors'
import quizSelectors from './../../selectors'
import {selectors as paperSelectors} from './../../papers/selectors'
import {utils as paperUtils} from './../../papers/utils'
import {ScoreGauge} from './../../../components/score-gauge'

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
              <h2 className="h4">{tex('attempt_end_title')}</h2>
              <p>{tex('attempt_end_info')}</p>
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
                  label: tex('exercise_restart'),
                  target: '/play',
                  exact: true,
                  primary: true,
                  displayed: hasMoreAttempts
                }, {
                  name: 'correction',
                  type: LINK_BUTTON,
                  icon: 'fa fa-fw fa-check-double',
                  label: tex('view_paper'),
                  target: `/papers/${props.paper.id}`,
                  displayed: showCorrection,
                  primary: true
                }, {
                  name: 'statistics',
                  type: LINK_BUTTON,
                  icon: 'fa fa-fw fa-bar-chart',
                  label: tex('statistics'),
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
    paper: playerSelectors.paper(state),
    endMessage: playerSelectors.quizEndMessage(state),
    endNavigation: playerSelectors.quizEndNavigation(state),
    answers: playerSelectors.answers(state),
    showStatistics: quizSelectors.parameters(state).showStatistics,
    maxAttempts: quizSelectors.parameters(state).maxAttempts,
    maxAttemptsPerDay: quizSelectors.parameters(state).maxAttemptsPerDay,
    userPaperCount: quizSelectors.meta(state).userPaperCount,
    userPaperDayCount: quizSelectors.meta(state).userPaperDayCount
  })
)(AttemptEndComponent)

export {
  AttemptEnd
}
