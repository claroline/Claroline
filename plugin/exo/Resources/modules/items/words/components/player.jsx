import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Textarea} from '#/main/core/layout/form/components/field/textarea'

const WordsPlayer = (props) =>
  <Textarea
    id={`open-${props.item.id}-data`}
    value={props.answer}
    onChange={(value) => props.onChange(value)}
  />

WordsPlayer.propTypes = {
  item: T.shape({
    id: T.string.isRequired
  }).isRequired,
  answer: T.string,
  onChange: T.func.isRequired
}

WordsPlayer.defaultProps = {
  answer: ''
}

export {
  WordsPlayer
}
