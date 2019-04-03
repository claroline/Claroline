import React from 'react'
import {PropTypes as T} from 'prop-types'

import {HtmlInput} from '#/main/app/data/types/html/components/input'

const OpenPlayer = (props) =>
  <HtmlInput
    id={`open-${props.item.id}-data`}
    value={props.answer}
    disabled={props.disabled}
    onChange={(value) => props.disabled ? false : props.onChange(value)}
  />

OpenPlayer.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    contentType: T.string.isRequired,
    maxLength: T.number.isRequired
  }).isRequired,
  answer: T.string,
  disabled: T.bool.isRequired,
  onChange: T.func.isRequired
}

OpenPlayer.defaultProps = {
  answer: '',
  disabled: false
}

export {
  OpenPlayer
}
