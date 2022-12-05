import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {TooltipOverlay} from '#/main/app/overlays/tooltip/components/overlay'

const FormStatus = props =>
  <TooltipOverlay
    id={props.id}
    tip={trans(props.validating ? 'form_validating_desc' : 'form_not_validating_desc')}
    position={props.position}
  >
    <span className={classes('validation-status fa fa-fw', {
      'fa-warning text-danger': props.validating,
      'fa-clock text-warning': !props.validating
    })} />
  </TooltipOverlay>

FormStatus.propTypes = {
  id: T.string.isRequired,
  position: T.oneOf(['left', 'top', 'right', 'bottom']),
  validating: T.bool.isRequired
}

FormStatus.defaultProps = {
  position: 'bottom'
}

export {
  FormStatus
}
