import React from 'react'
import {PropTypes as T} from 'prop-types'

import {transChoice} from '#/main/core/translation'

const ListCount = props =>
  <div className="count">
    {transChoice('list_results_count', props.totalResults, {count: props.totalResults}, 'platform')}
  </div>

ListCount.propTypes = {
  totalResults: T.number.isRequired
}

export {
  ListCount
}
