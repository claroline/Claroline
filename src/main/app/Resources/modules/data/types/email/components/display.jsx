import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'
import {trans} from '#/main/app/intl'
import {Email} from '#/main/app/components/email'

const EmailDisplay = (props) => {
  if (isEmpty(props.data)) {
    return (
      <span className="text-secondary d-block" role="presentation">{trans('empty_value')}</span>
    )
  }

  return (
    <Email email={props.data} className="d-block" />
  )
}

EmailDisplay.propTypes = {
  data: T.string.isRequired
}

export {
  EmailDisplay
}
