import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'

const StringDisplay = (props) => {
  if (isEmpty(props.data)) {
    return (
      <span id={props.id} className="string-display data-details-empty">{props.placeholder || trans('empty_value')}</span>
    )
  }

  return (
    <div id={props.id} className="string-display text-justify">
      {props.data}
    </div>
  )
}

StringDisplay.propTypes = {
  id: T.string.isRequired,
  data: T.string,
  placeholder: T.string
}

export {
  StringDisplay
}
