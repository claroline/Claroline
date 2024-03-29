import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Metadata as ItemMetadata} from '#/plugin/exo/items/components/metadata.jsx'
// import {Hint} from '#/plugin/exo/items/components/hint.jsx'

import {ContentHtml} from '#/main/app/content/components/html'
import {trans, transChoice} from '#/main/app/intl/translation'

const UsedHint = props =>
  <div className="used-hint">
    <span className="fa fa-fw fa-lightbulb" />
    <ContentHtml className="hint-text">
      {props.value}
    </ContentHtml>

    {props.penalty > 0 &&
      <span className="badge hint-penalty-info">
        {transChoice('hint_penalty', props.penalty, {count: props.penalty}, 'quiz')}
      </span>
    }
  </div>

UsedHint.propTypes = {
  value: T.string.isRequired,
  penalty: T.number
}

const Hint = props =>
  <button
    type="button"
    className="btn btn-outline-secondary w-100 hint-btn text-start"
    onClick={props.showHint}
  >
    <span className="fa fa-fw fa-lightbulb"/>
    {trans('hint', {number: props.number}, 'quiz')}

    {props.penalty > 0 &&
      <span className="text-danger hint-penalty-info">
        {transChoice('hint_penalty', props.penalty, {count: props.penalty}, 'quiz')}
      </span>
    }
  </button>

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
    <div className="item-hints">
      {hints}
    </div>
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
