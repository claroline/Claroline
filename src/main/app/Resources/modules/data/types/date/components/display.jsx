import React from 'react'
import {PropTypes as T} from 'prop-types'

import {render} from '#/main/app/data/types/date/utils'

const DateDisplay = (props) => {
  if (props.data) {
    return (
      <div className="date-display">
        {render(props.data, {time: props.time})}
      </div>
    )
  }

  return null
}

DateDisplay.propTypes = {
  data: T.string.isRequired,
  time: T.bool
}

export {
  DateDisplay
}
