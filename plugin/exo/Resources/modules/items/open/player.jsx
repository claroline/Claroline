import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Textarea} from '#/main/core/layout/form/components/field/textarea.jsx'

export const OpenPlayer = (props) =>
  <Textarea
    id={`open-${props.item.id}-data`}
    value={props.answer}
    onChange={(value) => props.onChange(value)}
  />

OpenPlayer.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    contentType: T.string.isRequired,
    maxLength: T.number.isRequired
  }).isRequired,
  answer: T.string,
  onChange: T.func.isRequired
}

OpenPlayer.defaultProps = {
  answer: ''
}
