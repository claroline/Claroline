import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {HtmlText} from '#/main/core/layout/components/html-text'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {LINK_BUTTON, URL_BUTTON} from '#/main/app/buttons'

import {ScoreGauge} from '#/plugin/exo/components/score-gauge'
import {utils as paperUtils} from '#/plugin/exo/quiz/papers/utils'
import {selectors as paperSelectors} from '#/plugin/exo/quiz/papers/selectors'

// TODO : merge with AttemptEnd

const PlayerRestrictions = props => {
  const showScore = paperUtils.showScore(false, props.lastAttempt.finished, paperSelectors.showScoreAt(props.lastAttempt), paperSelectors.showCorrectionAt(props.lastAttempt), paperSelectors.correctionDate(props.lastAttempt))
  const showCorrection = paperUtils.showCorrection(false, props.lastAttempt.finished, paperSelectors.showCorrectionAt(props.lastAttempt), paperSelectors.correctionDate(props.lastAttempt))
  const answers = Object.keys(props.lastAttempt.answers).map(key => props.lastAttempt.answers[key])

  return (
    <div className="quiz-player">
      <div className="row">
        {showScore &&
          <div className="col-md-3 text-center">
            <ScoreGauge
              userScore={paperUtils.computeScore(props.lastAttempt, answers)}
              maxScore={paperSelectors.paperScoreMax(props.lastAttempt)}
            />
          </div>
        }

        <div className={showScore ? 'col-md-9':'col-md-12'}>
          {props.message ?
            <HtmlText>{props.message}</HtmlText> :
            <div>
              <h2 className="h4">{trans('max_attempts_reached_title', {}, 'quiz')}</h2>
              <p>{trans('max_attempts_reached_info', {}, 'quiz')}</p>
            </div>
          }

          <Toolbar
            buttonName="btn btn-block btn-emphasis"
            toolbar="restart correction statistics home"
            actions={[
              {
                name: 'correction',
                type: LINK_BUTTON,
                icon: 'fa fa-fw fa-check-double',
                label: trans('view_paper', {}, 'quiz'),
                target: `/papers/${props.lastAttempt.id}`,
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
        </div>
      </div>
    </div>
  )
}

PlayerRestrictions.propTypes = {
  showStatistics: T.bool,
  workspaceId: T.number,
  message: T.string,
  lastAttempt: T.shape({ // TODO : paper propTypes
    id: T.string.isRequired,
    finished: T.bool.isRequired,
    answers: T.array.isRequired
  }),
  accessErrors: T.shape({
    maxAttemptsReached: T.bool
  })
}

export {
  PlayerRestrictions
}
