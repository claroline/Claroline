import React, {createElement, Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'

import {displayDate, displayDuration, trans} from '#/main/app/intl'
import {precision} from '#/main/app/intl/number'
import {Badge} from '#/main/app/components/badge'

import {EvaluationScore} from '#/main/evaluation/components/score'
import {EvaluationStatus} from '#/main/evaluation/components/status'
import {constants} from '#/main/evaluation/constants'
import {UserEvaluation} from '#/main/evaluation/prop-types'
import isEmpty from 'lodash/isEmpty'
import {Button} from '#/main/app/action'

const EvaluationListItem = ({
  title,
  evaluation,
  meta = [],
  primaryAction = null
}) => {
  let duration = evaluation.duration
  if (!duration && evaluation.estimatedDuration) {
    duration = evaluation.estimatedDuration
  }

  return createElement(primaryAction ? Button : 'div', Object.assign({className: 'py-3 d-flex flex-row gap-4 w-100 text-start'}, primaryAction || {}),
    <>
      <div role="presentation">
        <b>{title}</b>
        <div className={classes('d-flex gap-2 text-body-secondary fs-sm mt-2')} role="presentation">
          {meta.map(metaItem =>
            <Fragment key={metaItem}>
              {metaItem}
              <span aria-hidden={true}>-</span>
            </Fragment>
          )}

          <div role="presentation">
            <span className="fa fa-calendar me-2" aria-hidden={true} />
            {get(evaluation, 'lastActivityAt') ?
              displayDate(get(evaluation, 'lastActivityAt'), true, true) :
              <em>{trans('no_activity', {}, 'evaluation')}</em>
            }
          </div>

          {(duration && 0 !== duration) ?
            <>
              <span aria-hidden={true}>-</span>
              <div role="presentation">
                <span className="fa fa-clock me-2" aria-hidden={true} />
                {displayDuration(duration)}
              </div>
            </> : null
          }
        </div>
      </div>

      {evaluation.displayScore &&
        <div className="ms-auto d-flex flex-column text-end" style={{minWidth: '8rem'}}>
          <span className="fs-sm text-body-tertiary d-block mb-1">{trans('score')}</span>
          <EvaluationScore
            className="ms-auto"
            size="lg"
            score={get(evaluation, 'displayScore.current')}
            scoreMax={get(evaluation, 'displayScore.total')}
          />
        </div>
      }

      <div className={classes('d-flex flex-column', isEmpty(evaluation.displayScore) && 'ms-auto')} style={{minWidth: '8rem'}}>
        <span className="fs-sm text-body-tertiary  d-block mb-1">{trans('status')}</span>

        {constants.EVALUATION_STATUS_INCOMPLETE === evaluation.status ?
          <Badge className="fs-base me-auto" variant="info" subtle={true}>
            {precision(evaluation.progression || 0, 1)}%
          </Badge> :
          <EvaluationStatus className="fs-base me-auto" status={evaluation.status} subtle={true} />
        }
      </div>

      <span className={classes('fa fa-fw text-body-tertiary align-self-center', !!primaryAction && 'fa-chevron-right')} aria-hidden={true} />
    </>
  )
}

EvaluationListItem.propTypes = {
  // The title of the activity which has generated the evaluation
  title: T.string.isRequired,
  meta: T.array,
  evaluation: T.shape(
    UserEvaluation.propTypes
  ).isRequired,
  primaryAction: T.shape({
    // action types
  })
}

export {
  EvaluationListItem
}
