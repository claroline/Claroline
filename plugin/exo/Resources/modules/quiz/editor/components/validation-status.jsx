import React from 'react'
import {PropTypes as T} from 'prop-types'

import {tex} from '#/main/app/intl/translation'
import {TooltipOverlay} from '#/main/app/overlay/tooltip/components/overlay'

const ValidationStatus = props =>
  <TooltipOverlay
    id={props.id}
    position={props.position}
    tip={tex(props.validating ?
      'editor_validating_desc' :
      'editor_not_validating_desc'
    )}
  >
    <span className={props.validating ?
      'validation-status text-danger fa fa-fw fa-warning' :
      'validation-status text-warning fa fa-fw fa-clock-o'
    }/>
  </TooltipOverlay>

ValidationStatus.propTypes = {
  id: T.string.isRequired,
  validating: T.bool.isRequired,
  position: T.oneOf(['left', 'top', 'right', 'bottom'])
}

ValidationStatus.defaultProps = {
  position: 'right'
}

export {
  ValidationStatus
}
