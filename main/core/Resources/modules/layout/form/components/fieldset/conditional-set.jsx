import React from 'react'
import {PropTypes as T} from 'prop-types'

import {SubSet} from '#/main/core/layout/form/components/fieldset/sub-set.jsx'

/**
 * Renders a fieldset if a condition is met.
 */
const ConditionalSet = props => props.condition &&
  <SubSet>
    {props.children}
  </SubSet>

ConditionalSet.propTypes = {
  condition: T.bool,
  children: T.any.isRequired
}

export {
  ConditionalSet
}