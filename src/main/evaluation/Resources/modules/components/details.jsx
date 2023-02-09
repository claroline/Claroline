import React from 'react'
import {PropTypes as T} from 'prop-types'

import {number, trans} from '#/main/app/intl'
import {ScoreGauge} from '#/main/core/layout/gauge/components/score'
import {LiquidGauge} from '#/main/core/layout/gauge/components/liquid-gauge'

import {constants} from '#/main/evaluation/constants'

const EvaluationDetails = (props) => {
  let successScore
  if (props.showScore && props.successScore) {
    successScore = (props.scoreMax || props.evaluation.scoreMax) * props.successScore / 100
  }

  return (
    <div className="panel panel-default">
      <div className="panel-body text-center">
        {props.showScore &&
          <ScoreGauge
            type="user"
            value={props.evaluation.score && props.scoreMax ? (props.evaluation.score / props.evaluation.scoreMax) * props.scoreMax : props.evaluation.score}
            total={props.scoreMax || props.evaluation.scoreMax}
            width={140}
            height={140}
            displayValue={value => undefined === value || null === value ? '?' : number(value)+''}
          />
        }

        {!props.showScore &&
          <LiquidGauge
            id={`user-progression-${props.evaluation.id}`}
            type="user"
            value={props.evaluation.progression || 0}
            displayValue={(value) => number(value) + '%'}
            width={140}
            height={140}
          />
        }

        <h4 className="user-progression-status">
          {props.statusTexts[props.evaluation.status] ?
            props.statusTexts[props.evaluation.status] :
            constants.EVALUATION_STATUSES[props.evaluation.status]
          }
        </h4>
      </div>

      {(props.estimatedDuration || successScore || (props.details && 0 !== props.details.length)) &&
        <ul className="list-group list-group-values">
          {props.estimatedDuration &&
            <li className="list-group-item">
              {trans('estimated_duration')}
              <span className="value">{props.estimatedDuration} {trans('minutes')}</span>
            </li>
          }

          {successScore &&
            <li className="list-group-item">
              {trans('score_to_pass')}
              <span className="value">{successScore} ({props.successScore} %)</span>
            </li>
          }

          {props.details && props.details.map((info, index) =>
            <li key={index} className="list-group-item">
              {info[0]}
              <span className="value">{info[1]}</span>
            </li>
          )}
        </ul>
      }
    </div>
  )
}

EvaluationDetails.defaultProps = {
  status: constants.EVALUATION_STATUS_NOT_ATTEMPTED,
  statusTexts: {}
}

EvaluationDetails.propTypes = {
  statusTexts: T.object,
  showScore: T.bool,
  scoreMax: T.number,
  successScore: T.number,
  evaluation: T.shape({
    id: T.string.isRequired,
    status: T.string.isRequired,
    score: T.number,
    scoreMax: T.number,
    progression: T.number
  }).isRequired,
  estimatedDuration: T.number,
  details: T.arrayOf(
    T.arrayOf(T.string)
  )
}

export {
  EvaluationDetails
}