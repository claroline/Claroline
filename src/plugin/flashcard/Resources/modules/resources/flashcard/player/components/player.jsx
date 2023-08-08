import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import Carousel from 'react-bootstrap/Carousel'

import {asset} from '#/main/app/config/asset'
import {trans} from '#/main/app/intl/translation'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

import {selectors} from '#/plugin/flashcard/resources/flashcard/player/store'
import {Card as CardTypes} from '#/plugin/flashcard/resources/flashcard/prop-types'

const PlayerComponent = props => {
  if (0 === props.cards.length) {
    return (
      <ContentPlaceholder
        size="lg"
        icon="fa fa-image"
        title={trans('no_card', {}, 'flashcard')}
      />
    )
  }

  let activeIndex = props.cards.findIndex(card => card.id === props.activeCard)
  if (-1 === activeIndex) {
    activeIndex = 0
  }

  return (
    <Carousel
      className="row slideshow-carousel"
      defaultActiveIndex={activeIndex}

      prevIcon={<span className="fa fa-chevron-left" />}
      prevLabel={trans('previous')}
      nextIcon={<span className="fa fa-chevron-right" />}
      nextLabel={trans('next')}
    >
      {props.cards.map(card => {

        return (
          <Carousel.Item
            key={card.id}
          >
            <img src={asset(card.content.url)} alt={card.title} />

            {(card.meta.title || card.meta.description) &&
            <Carousel.Caption>
              <h3>{card.meta.title}</h3>
              <p>{card.meta.description}</p>
            </Carousel.Caption>
            }
          </Carousel.Item>
        )
      })}
    </Carousel>
  )
}

PlayerComponent.propTypes = {
  activeCard: T.string,
  cards: T.arrayOf(T.shape(
    CardTypes.propTypes
  )).isRequired
}

const Player = connect(
  (state) => ({
    cards: selectors.cards(state)
  })
)(PlayerComponent)

export {
  Player
}
