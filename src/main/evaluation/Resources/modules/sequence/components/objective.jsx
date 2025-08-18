import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl'
import {Html} from '#/main/app/components/html'

const SequenceObjective = ({className, objective}) =>
  <div className={classes('p-4 bg-primary-subtle text-primary-emphasis rounded-3', className)}>
    <div className="page-section-title h6">{trans('learner_objective', {}, 'evaluation')}</div>
    <Html className="content-text">
      {objective}
    </Html>
  </div>

SequenceObjective.propTypes = {
  className: T.string,
  objective: T.string.isRequired
}

export {
  SequenceObjective
}
