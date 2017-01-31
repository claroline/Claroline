import React, {PropTypes as T} from 'react'
import {tex} from './../../../utils/translate'
import {TooltipElement} from './../../../components/form/tooltip-element.jsx'

export const ValidationStatus = props =>
  <TooltipElement
    id={props.id}
    position="right"
    tip={tex(props.validating ?
      'editor_validating_desc' :
      'editor_not_validating_desc'
    )}
  >
    <span className={props.validating ?
      'error-text fa fa-warning' :
      'warning-text fa fa-clock-o'
    }/>
  </TooltipElement>

ValidationStatus.propTypes = {
  id: T.string.isRequired,
  validating: T.bool.isRequired
}
