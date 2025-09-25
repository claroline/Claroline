import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'

import {MODAL_BUTTON} from '#/main/app/buttons'

import {EvaluationListItem} from '#/main/evaluation/components/list-item'
import {MODAL_USER_PROGRESSION} from '#/main/evaluation/resource/modals/user-progression'
import {SequenceEvaluation} from '#/main/evaluation/sequence/prop-types'

const UserProgressionArchives = (props) => {
  return (
    <ul className="list-unstyled mb-0">
      {props.archives.map((evaluation, index) =>
        <li key={evaluation.id} className={classes(0 !== index && 'border-top')}>
          <EvaluationListItem
            title={get(evaluation, 'resourceNode.name')}
            evaluation={evaluation}
            primaryAction={{
              type: MODAL_BUTTON,
              modal: [MODAL_USER_PROGRESSION, {
                evaluation: evaluation
              }],
              onClick: props.fadeModal
            }}
          />
        </li>
      )}
    </ul>
  )
}

UserProgressionArchives.propTypes = {
  archives: T.arrayOf(T.shape(
    SequenceEvaluation.propTypes
  )),
  fadeModal: T.func.isRequired
}

export {
  UserProgressionArchives
}
