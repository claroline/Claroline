import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {HtmlInput} from '#/main/app/data/types/html/components/input'

const OpenPlayer = (props) =>
  <div>
    <HtmlInput
      id={`open-${props.item.id}-data`}
      value={props.answer}
      disabled={props.disabled}
      onChange={(value) => props.disabled ? false : props.onChange(value)}
    />
    {0 < props.item.maxLength &&
      <div className="pull-right">
        {trans('remaining_characters', {}, 'quiz')} : {props.item.maxLength - props.answer.replace('&nbsp;', ' ').replace(/<[^>]*>/g, '').length}
      </div>
    }
  </div>

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
