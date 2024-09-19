import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {ContentHtml} from '#/main/app/content/components/html'
import {trans, transChoice} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Badge} from '#/main/app/components/badge'

import {Metadata as ItemMetadata} from '#/plugin/exo/items/components/metadata'

const UsedHint = props =>
  <li className="list-group-item list-group-item-info d-flex gap-3 align-items-baseline">
    <ContentHtml className="hint-text flex-fill">
      {props.value}
    </ContentHtml>

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
  <div className="quiz-item item-player">
    <ItemMetadata
      showTitle={props.showTitle}
      item={props.item}
      numbering={props.numbering}
    />

    <hr className="item-content-separator" />

    {props.children}

    {props.item.hints && 0 !== props.item.hints.length &&
      <hr className="item-content-separator" />
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
  item: T.shape({
    id: T.string.isRequired,
    title: T.string,
    description: T.string.isRequired,
    content: T.string.isRequired,
    hints: T.array
  }).isRequired,
  showTitle: T.bool,
  showHint: T.func.isRequired,
  usedHints: T.array.isRequired,
  children: T.node.isRequired,
  numbering: T.any
}

ItemPlayer.defaultProps = {
  usedHints: []
}

export {ItemPlayer}
