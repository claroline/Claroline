import React from 'react'
import {PropTypes as T} from 'prop-types'

import {transChoice} from '#/main/app/intl/translation'

const ListCount = props =>
  <div className="count text-body-secondary">
    {transChoice('list_results_count', props.totalResults, {count: props.totalResults}, 'platform')}
  </div>

ListCount.propTypes = {
  totalResults: T.number.isRequired
}

export {
  ListCount
}
