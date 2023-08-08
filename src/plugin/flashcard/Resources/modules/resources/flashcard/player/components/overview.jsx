import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {asset} from '#/main/app/config/asset'
import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {LinkButton} from '#/main/app/buttons/link/components/button'
import {Button} from '#/main/app/action/components/button'

import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {ContentHtml} from '#/main/app/content/components/html'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

import {selectors} from '#/plugin/slideshow/resources/slideshow/player/store'
import {Card as CardTypes} from '#/plugin/flashcard/resources/flashcard/prop-types'

const OverviewComponent = props =>
  <section className="resource-section resource-overview">
    <h2 className="sr-only">{trans('resource_overview', {}, 'resource')}</h2>

    {props.overviewMessage &&
      <section className="resource-info">
        <h3 className="h2">{trans('resource_overview_info', {}, 'resource')}</h3>

        <div className="card mb-3">
          <ContentHtml className="card-body">{props.overviewMessage}</ContentHtml>
        </div>
      </section>
    }

    <section>
      <h3 className="h2">{trans('cards', {}, 'flashcard')}</h3>

      {0 === props.cards.length &&
        <ContentPlaceholder
          size="lg"
          icon="fa fa-image"
          title={trans('no_card', {}, 'flashcard')}
        />
      }

      {0 !== props.cards.length &&
        <ul className="slides">
          {props.cards.map(card =>
            <li key={card.id} className="slide-preview">
              <LinkButton target={`${props.path}/play/${card.id}`}>
                <img src={asset(card.content.text)} alt={card.title} className="img-thumbnail"/>
              </LinkButton>
            </li>
          )}
        </ul>
      }
    </section>

    <Button
      type={LINK_BUTTON}
      className="btn btn-primary w-100"
      icon="fa fa-fw fa-play"
      label={trans('start_flashcard', {}, 'flashcard')}
      target={`${props.path}/play`}
      primary={true}
      size="lg"
      disabled={0 === props.cards.length}
    />
  </section>

OverviewComponent.propTypes = {
  path: T.string,
  overviewMessage: T.string,
  cards: T.arrayOf(T.shape(
    CardTypes.propTypes
  ))
}

const Overview = connect(
  (state) => ({
    path: resourceSelectors.path(state),
    overviewMessage: selectors.overviewMessage(state),
    cards: selectors.cards(state)
  })
)(OverviewComponent)

export {
  Overview
}
