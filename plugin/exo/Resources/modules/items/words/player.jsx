import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Textarea} from '#/main/core/layout/form/components/textarea.jsx'

export const WordsPlayer = (props) =>
  <Textarea
    id={`open-${props.item.id}-data`}
    content={props.answer}
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
