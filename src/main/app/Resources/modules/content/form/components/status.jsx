import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {TooltipOverlay} from '#/main/app/overlays/tooltip/components/overlay'

const FormStatus = props =>
  <TooltipOverlay
    id={props.id}
    tip={trans(props.validating ? 'form_validating_desc' : 'form_not_validating_desc')}
    position={props.tooltip}
  >
    <span className={classes(props.className, 'validation-status fa fa-exclamation-circle', {
      'text-danger': props.validating,
      'text-warning': !props.validating
    })} />
  </TooltipOverlay>

FormStatus.propTypes = {
  className: T.string,
  id: T.string.isRequired,
  tooltip: T.oneOf(['left', 'top', 'right', 'bottom']),
  validating: T.bool.isRequired
}

FormStatus.defaultProps = {
  tooltip: 'bottom'
}

export {
  FormStatus
}
