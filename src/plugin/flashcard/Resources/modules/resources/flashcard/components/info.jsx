import React from 'react'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {ContentCounter} from '#/main/app/content/components/counter'
import {schemeCategory20c} from '#/main/theme/color/utils'

import {selectors} from '#/plugin/flashcard/resources/flashcard/store'

const FlashcardInfoComponent = (props) =>
  <section className="flashcards-info">
    <ContentCounter
      icon="fa fa-layer-group"
      label={trans('cartes', {}, 'flashcard')}
      color={schemeCategory20c[1]}
      value={props.cards.length}
    />

    <ContentCounter
      icon="fa fa-vr-cardboard"
      label={trans('view', {}, 'flashcard')}
      color={schemeCategory20c[16]}
      value={props.cards.filter((card) => props.progress.find((progression) => progression.flashcard.id === card.id)).length}
    />

    <ContentCounter
      icon="fa fa-check"
      label={trans('check', {}, 'flashcard')}
      color={schemeCategory20c[10]}
      value={props.cards.filter( (card) => props.progress.find( (progression) =>
        progression.flashcard.id === card.id && progression && progression.isSuccessful === true
      ) ).length}
    />

    <ContentCounter
      icon="fa fa-xmark"
      label={trans('fail', {}, 'flashcard')}
      color={schemeCategory20c[5]}
      value={props.cards.filter( (card) => props.progress.find( (progression) =>
        progression.flashcard.id === card.id && progression && progression.isSuccessful === false
      )).length}
    />
  </section>

const FlashcardInfo = connect(
  (state) => ({
    cards: selectors.cards(state),
    progress: selectors.flashcardDeckProgression(state)
  })
)(FlashcardInfoComponent)

export {
  FlashcardInfo
}
