import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Textarea} from '#/main/core/layout/form/components/field/textarea'

const WordsPlayer = (props) =>
  <Textarea
    id={`open-${props.item.id}-data`}
    value={props.answer}
    disabled={props.disabled}
    onChange={(value) => props.disabled ? false : props.onChange(value)}
  />

WordsPlayer.propTypes = {
  item: T.shape({
    id: T.string.isRequired
  }).isRequired,
  answer: T.string,
  disabled: T.bool.isRequired,
  onChange: T.func.isRequired
}

WordsPlayer.defaultProps = {
  answer: '',
  disabled: false
}

export {
  WordsPlayer
}
