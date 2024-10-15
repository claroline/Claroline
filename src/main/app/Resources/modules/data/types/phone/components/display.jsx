import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl'
import {Phone} from '#/main/app/components/phone'

const PhoneDisplay = (props) => {
  if (isEmpty(props.data)) {
    return (
      <span className="text-secondary d-block" role="presentation">{trans('empty_value')}</span>
    )
  }

  return (
    <Phone phone={props.data} className="d-block" />
  )
}

PhoneDisplay.propTypes = {
  data: T.string
}

export {
  PhoneDisplay
}
