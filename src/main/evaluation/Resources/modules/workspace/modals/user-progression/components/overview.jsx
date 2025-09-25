import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {MODAL_BUTTON} from '#/main/app/buttons'
import {EmptyState} from '#/main/app/components/empty-state'

import {EvaluationListItem} from '#/main/evaluation/components/list-item'
import {MODAL_USER_PROGRESSION} from '#/main/evaluation/sequence/modals/user-progression'
import {SequenceEvaluation} from '#/main/evaluation/sequence/prop-types'

const UserProgressionOverview = (props) => {
  if (isEmpty(props.progression)) {
    return (
      <EmptyState
        className="py-5"
        title={trans('L\'utilisateur n\'a commencé aucune activité pour le moment')}
      />
    )
  }

  return (
    <ul className="list-unstyled mb-0">
      {props.progression.map((sequenceEvaluation, index) =>
        <li key={sequenceEvaluation.id} className={classes(0 !== index && 'border-top')}>
          <EvaluationListItem
            title={get(sequenceEvaluation, 'sequence.name')}
            evaluation={sequenceEvaluation}
            primaryAction={{
              type: MODAL_BUTTON,
              modal: [MODAL_USER_PROGRESSION, {
                evaluation: sequenceEvaluation
              }]
            }}
          />
        </li>
      )}
    </ul>
  )
}

UserProgressionOverview.propTypes = {
  progression: T.arrayOf(T.shape(
    SequenceEvaluation.propTypes
  ))
}

export {
  UserProgressionOverview
}
