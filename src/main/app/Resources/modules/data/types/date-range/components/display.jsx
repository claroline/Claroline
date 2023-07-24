import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {render} from '#/main/app/data/types/date-range/utils'

const DateRangeDisplay = (props) => {
  if (!isEmpty(props.data)) {
    return (
      <div className="date-range-display mb-3">
        {render(props.data, {time: props.time})}
      </div>
    )
  }

  return null
}

DateRangeDisplay.propTypes = {
  data: T.array,
  time: T.bool
}

export {
  DateRangeDisplay
}
