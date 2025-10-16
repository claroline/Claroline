import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl'
import {ProgressBar} from '#/main/app/components/progress-bar'
import {Datetime} from '#/main/app/components/date'
import {ModalButton} from '#/main/app/buttons'

import {constants} from '#/main/evaluation/constants'
import {EvaluationScore} from '#/main/evaluation/components/score'

const EvaluationProgression = ({className, modal, evaluation = {}}) => {
  let statusText = trans('completion', {current: evaluation.progression || 0}, 'evaluation')
  if (evaluation.progression >= 100) {
    statusText = constants.EVALUATION_STATUSES_SHORT[evaluation.status]
  }

  return (
    <div className={classes('d-flex flex-row align-items-center gap-4 bg-body-tertiary rounded-3 py-3 px-4', className)}>
      {evaluation.displayScore &&
        <EvaluationScore
          score={evaluation.displayScore.current}
          scoreMax={evaluation.displayScore.total}
          condensed={false}
          size="lg"
        />
      }

      <div className="flex-fill" role="presentation">
        <ProgressBar value={evaluation.progression || 0} size="xs" variant="learning" />

        <div className="d-flex flex-row gap-3 flex-wrap align-items-center mt-2" role="presentation">
          <div className="" role="presentation">
            <b className="d-block ">{statusText}</b>
            <small className="text-body-secondary" role="presentation">
              {evaluation.lastActivityAt ?
                (<>{trans('last_activity_at')} <Datetime value={evaluation.lastActivityAt} time={true} long={true} /></>)
                : trans('no_user_activity', {}, 'evaluation')
              }
            </small>
          </div>

          {evaluation.status && ![constants.EVALUATION_STATUS_NOT_ATTEMPTED, constants.EVALUATION_STATUS_UNKNOWN].includes(evaluation.status) &&
            <ModalButton modal={[modal, {evaluation: evaluation}]} className="btn btn-link ms-auto">
              {trans('see_detail', {}, 'actions')}
              <span className="ms-2 fa fa-arrow-right" aria-hidden={true} />
            </ModalButton>
          }
        </div>
      </div>
    </div>
  )
}

EvaluationProgression.propTypes = {
  className: T.string,
  evaluation: T.shape({
    progression: T.number,
    status: T.string,
    lastActivity: T.string,
    displayScore: T.shape({
      current: T.number,
      total: T.number.isRequired
    })
  }),
  modal: T.string.isRequired
}

export {
  EvaluationProgression
}
