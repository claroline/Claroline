import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'

const StringDisplay = (props) => {
  if (isEmpty(props.data)) {
    return (
      <div id={props.id} className="string-display text-secondary">{props.placeholder || trans('empty_value')}</div>
    )
  }

  return (
    <div id={props.id} className="string-display text-justify" style={props.long ? {whiteSpace: 'pre-wrap'} : undefined}>
      {props.data}
    </div>
  )
}

StringDisplay.propTypes = {
  id: T.string.isRequired,
  data: T.string,
  placeholder: T.string,
  long: T.bool
}

export {
  StringDisplay
}
