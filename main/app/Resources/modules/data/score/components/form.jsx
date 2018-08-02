import React from 'react'
import {PropTypes as T} from 'prop-types'

import {NumberGroup} from '#/main/core/layout/form/components/group/number-group.jsx'

const ScoreForm = props =>
  <NumberGroup
    {...props}
    unit={props.max+''}
  />

ScoreForm.propTypes = {
  value: T.number,
  max: T.number.isRequired
}
export {
  ScoreForm
}
