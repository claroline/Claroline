import React from 'react'
import {PropTypes as T} from 'prop-types'

import {number} from '#/main/app/intl/number'
import {ProgressBar} from '#/main/app/content/components/progress-bar'

const ProgressionDisplay = (props) => {
  const value = number(props.data || 0)

  return (
    <ProgressBar
      id={props.id}
      className="progression-display"
      type={props.type}
      value={value}
    />
  )
}

ProgressionDisplay.propTypes = {
  id: T.string.isRequired,
  data: T.number,
  type: T.oneOf(['success', 'info', 'warning', 'danger', 'user'])
}

export {
  ProgressionDisplay
}
