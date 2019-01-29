import React from 'react'
import {PropTypes as T} from 'prop-types'

import {NumberInput} from '#/main/app/data/types/number/components/input'

const ScoreInput = props =>
  <NumberInput
    {...props}
    unit={props.max+''}
  />

// TODO : correct propTypes
ScoreInput.propTypes = {
  value: T.number,
  max: T.number.isRequired
}
export {
  ScoreInput
}
