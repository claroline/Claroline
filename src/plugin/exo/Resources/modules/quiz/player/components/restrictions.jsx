import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {ContentHtml} from '#/main/app/content/components/html'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {LINK_BUTTON, URL_BUTTON} from '#/main/app/buttons'
import {route} from '#/main/core/workspace/routing'
import {ScoreGauge} from '#/main/core/layout/gauge/components/score'

import {showCorrection, showScore} from '#/plugin/exo/resources/quiz/papers/restrictions'

// TODO : merge with AttemptEnd
// TODO : get correct admin flag

const PlayerRestrictions = props => {
  const showAttemptScore = showScore(props.lastAttempt, false)
  const showAttemptCorrection = showCorrection(props.lastAttempt, false)

  return (
    <div className="quiz-player">
      <div className="row">
        {showAttemptScore &&
          <div className="col-md-3 text-center">
            <ScoreGauge
              type="user"
              value={get(props.lastAttempt, 'score')}
              total={get(props.lastAttempt, 'total')}
              width={140}
              height={140}
              displayValue={value => undefined === value || null === value ? '?' : value+''}
            />
          </div>
        }

        <div className={showAttemptScore ? 'col-md-9':'col-md-12'}>
          {props.message ?
            <ContentHtml>{props.message}</ContentHtml> :
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
                target: `${props.path}/papers/${props.lastAttempt.id}`,
                displayed: showAttemptCorrection,
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
                type: URL_BUTTON,
                icon: 'fa fa-fw fa-home',
                label: trans('return-home', {}, 'actions'),
                target: '#'+route(props.workspace),
                displayed: !!props.workspace
              }
            ]}
          />
        </div>
      </div>
    </div>
  )
}

PlayerRestrictions.propTypes = {
  path: T.string,
  showStatistics: T.bool,
  workspace: T.object,
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
