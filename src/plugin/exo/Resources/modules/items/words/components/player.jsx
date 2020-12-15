import React from 'react'
import {PropTypes as T} from 'prop-types'

import {DateInput} from '#/main/app/data/types/date/components/input'
import {HtmlInput} from '#/main/app/data/types/html/components/input'

const WordsPlayer = (props) => {
  switch (props.item.contentType) {
    case 'date':
      return (
        <DateInput
          id={`open-${props.item.id}-data`}
          value={props.answer}
          disabled={props.disabled}
          onChange={(value) => props.disabled ? false : props.onChange(value)}
        />
      )

    case 'text':
    default:
      return (
        <HtmlInput
          id={`open-${props.item.id}-data`}
          value={props.answer}
          disabled={props.disabled}
          onChange={(value) => props.disabled ? false : props.onChange(value)}
        />
      )
  }
}

WordsPlayer.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    contentType: T.string.isRequired
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
