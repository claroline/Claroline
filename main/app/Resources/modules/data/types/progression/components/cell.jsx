import React from 'react'
import {PropTypes as T} from 'prop-types'

import {number} from '#/main/app/intl/number'
import {TooltipOverlay} from '#/main/app/overlays/tooltip/components/overlay'
import {ProgressBar} from '#/main/app/content/components/progress-bar'

const ProgressionCell = (props) => {
  const value = number(props.data || 0)

  return (
    <TooltipOverlay
      id={`tooltip-${props.id}`}
      tip={`${value} %`}
    >
      <ProgressBar
        id={props.id}
        size="sm"
        type={props.type}
        value={value}
      />
    </TooltipOverlay>
  )
}

ProgressionCell.propTypes = {
  id: T.string.isRequired,
  data: T.number,
  type: T.oneOf(['success', 'info', 'warning', 'danger', 'user'])
}

export {
  ProgressionCell
}
