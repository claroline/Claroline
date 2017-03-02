import React, {PropTypes as T} from 'react'

import {Metadata as ItemMetadata} from './../../../items/components/metadata.jsx'

import {tex, transChoice} from './../../../utils/translate'

const UsedHint = props =>
  <div className="well well-sm used-hint">
    <span className="fa fa-lightbulb-o"></span>
    <span className="hint-text" dangerouslySetInnerHTML={{__html: props.value}}></span>

    {props.penalty > 0 &&
      <small className="text-danger hint-penalty-info">
        &nbsp;(
          {transChoice('hint_penalty', props.penalty, {count: props.penalty}, 'ujm_exo')}
        )
      </small>
    }
  </div>

UsedHint.propTypes = {
  value: T.string.isRequired,
  penalty: T.number
}

const Hint = props =>
  <button
    type="button"
    className="btn btn-default btn-block hint-btn"
    onClick={props.showHint}
  >
    <span className="fa fa-eye"/>
    &nbsp;{tex('hint')}&nbsp;{props.number}

    {props.penalty > 0 &&
      <small className="text-danger hint-penalty-info">
        &nbsp;(
          {transChoice('hint_penalty', props.penalty, {count: props.penalty}, 'ujm_exo')}
        )
      </small>
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
  <div className="item-player">
    {props.item.title &&
      <h3 className="item-title">{props.item.title}</h3>
    }

    <ItemMetadata item={props.item} />

    {props.children}

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
  showHint: T.func.isRequired,
  usedHints: T.array.isRequired,
  children: T.node.isRequired
}

export {ItemPlayer}
