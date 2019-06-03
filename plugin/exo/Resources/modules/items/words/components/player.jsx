import React from 'react'
import {PropTypes as T} from 'prop-types'

import {HtmlInput} from '#/main/app/data/types/html/components/input'

const WordsPlayer = (props) =>
  <HtmlInput
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
