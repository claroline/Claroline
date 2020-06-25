import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans, displayDate} from '#/main/app/intl'
import {DateInput} from '#/main/app/data/types/date/components/input'
import {HtmlInput} from '#/main/app/data/types/html/components/input'
import {Calendar} from '#/main/core/layout/calendar/components/calendar'
import {ContentHelp} from '#/main/app/content/components/help'

const WordsPlayer = (props) => {
  switch (props.item.contentType) {
    case 'date':
      return (
        <div className="text-center">
          <h3 className="h4">{props.answer ? displayDate(props.answer) : trans('no_answer', {}, 'quiz')}</h3>

          <ContentHelp
            style={{textAlign: 'center'}}
            help={trans('item_words_calendar_help', {}, 'quiz')}
          />

          <Calendar
            id={`open-${props.item.id}-data`}
            selected={props.answer}
            disabled={props.disabled}
            onChange={(value) => props.disabled ? false : props.onChange(value)}
          />
        </div>
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
