import React, {createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans, transChoice} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Badge} from '#/main/app/components/badge'
import {Html} from '#/main/app/components/html'

import {Metadata as ItemMetadata} from '#/plugin/exo/items/components/metadata'
import {getComponent} from '#/plugin/exo/items/item-types'

const UsedHint = props =>
  <li className="list-group-item list-group-item-info d-flex gap-3 align-items-baseline">
    <Html className="hint-text flex-fill">
      {props.value}
    </Html>

    {props.penalty > 0 &&
      <Badge variant="info">{transChoice('hint_penalty', props.penalty, {count: props.penalty}, 'quiz')}</Badge>
    }
  </li>

UsedHint.propTypes = {
  value: T.string.isRequired,
  penalty: T.number
}

const Hint = props =>
  <li className="list-group-item d-flex align-items-baseline gap-3">
    {trans('hint', {number: props.number}, 'quiz')}

    {props.penalty > 0 &&
      <Badge className="ms-auto" variant="secondary" subtle={true}>
        {transChoice('hint_penalty', props.penalty, {count: props.penalty}, 'quiz')}
      </Badge>
    }

    <Button
      type={CALLBACK_BUTTON}
      className={classes('btn btn-body', !props.penalty && 'ms-auto')}
      size="sm"
      callback={props.showHint}
      label={trans('show', {}, 'actions')}
      confirm={{
        message: trans('hint_confirm_question', {}, 'quiz'),
        additional: props.penalty > 0 ? transChoice('hint_confirm_additional', props.penalty, {count: '<b class="fw-bold">'+props.penalty+'</b>'}, 'quiz') : undefined,
        button: trans('show', {}, 'actions')
      }}
    />
  </li>

Hint.propTypes = {
  penalty: T.number,
  number: T.number.isRequired,
  showHint: T.func.isRequired
}

const Hints = props => {
  const hints = props.hints.map((hint, index) => {
    const used = props.usedHints.find((usedHint) => usedHint.id === hint.id)
    if (used) {
      return (
        <UsedHint
          key={index}
          value={used.value}
          penalty={used.penalty}
        />
      )
    } else {
      return (
        <Hint
          key={index}
          number={index + 1}
          penalty={hint.penalty}
          showHint={() => props.showHint(hint)}
        />
      )
    }
  })

  return (
    <ul className="list-group">
      {hints}
    </ul>
  )
}

Hints.propTypes = {
  hints: T.arrayOf(T.shape({
    id: T.string.isRequired,
    penalty: T.number
  })).isRequired,
  usedHints: T.arrayOf(T.shape({
    id: T.string.isRequired,
    value: T.string.isRequired,
    penalty: T.number
  })).isRequired,
  showHint: T.func.isRequired
}

const ItemPlayer = props =>
  <div className={classes('quiz-item item-player', props.className)}>
    <ItemMetadata
      showTitle={props.showTitle}
      item={props.item}
      numbering={props.numbering}
    />

    <hr className="item-content-separator my-4" />

    {createElement(getComponent(props.item.type, 'player'), {
      item: props.item,
      answer: props.answer && props.answer.data ? props.answer.data : undefined,
      disabled: !props.editable && props.answer && 0 < props.answer.tries,
      onChange: (answerData) => props.updateAnswer(props.item.id, answerData)
    })}

    {props.item.hints && 0 !== props.item.hints.length &&
      <hr className="item-content-separator my-4" />
    }

    {props.item.hints && 0 !== props.item.hints.length &&
      <Hints
        hints={props.item.hints}
        usedHints={props.usedHints}
        showHint={(hint) => props.showHint(props.item.id, hint)}
      />
    }
  </div>

ItemPlayer.propTypes = {
  className: T.string,
  item: T.shape({
    id: T.string.isRequired,
    title: T.string,
    description: T.string.isRequired,
    content: T.string.isRequired,
    hints: T.array
  }).isRequired,
  answer: T.shape({
    tries: T.number,
    data: T.any
  }),
  showTitle: T.bool,
  showHint: T.func.isRequired,
  usedHints: T.array.isRequired,
  editable: T.bool,
  numbering: T.any,
  updateAnswer: T.func.isRequired
}

ItemPlayer.defaultProps = {
  usedHints: []
}

export {
  ItemPlayer
}
