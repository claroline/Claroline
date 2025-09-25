import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {MODAL_BUTTON} from '#/main/app/buttons'
import {EmptyState} from '#/main/app/components/empty-state'

import {EvaluationListItem} from '#/main/evaluation/components/list-item'
import {MODAL_USER_PROGRESSION} from '#/main/evaluation/resource/modals/user-progression'
import {ResourceEvaluation} from '#/main/evaluation/resource/prop-types'

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
      {props.progression.map((stepEvaluation, index) =>
        <li key={get(stepEvaluation, 'step.id')} className={classes(0 !== index && 'border-top')}>
          <EvaluationListItem
            title={get(stepEvaluation, 'step.name')}
            evaluation={stepEvaluation}
            primaryAction={stepEvaluation.resourceNode ? {
              type: MODAL_BUTTON,
              modal: [MODAL_USER_PROGRESSION, {
                evaluation: stepEvaluation
              }]
            } : undefined}
            meta={[
              get(stepEvaluation, 'resourceNode') ?
                trans(get(stepEvaluation, 'resourceNode.meta.type'), {}, 'resource') :
                trans('information')
            ]}
          />
        </li>
      )}
    </ul>
  )
}

UserProgressionOverview.propTypes = {
  progression: T.arrayOf(T.shape(
    ResourceEvaluation.propTypes
  ))
}

export {
  UserProgressionOverview
}
