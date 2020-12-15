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
import {Slide as SlideTypes} from '#/plugin/slideshow/resources/slideshow/prop-types'

// TODO : merge with standard overview when UserProgression will be implemented for slideshow

const OverviewComponent = props =>
  <section className="resource-section resource-overview">
    <h2 className="sr-only">{trans('resource_overview', {}, 'resource')}</h2>

    {props.overviewMessage &&
      <section className="resource-info">
        <h3 className="h2">{trans('resource_overview_info', {}, 'resource')}</h3>

        <div className="panel panel-default">
          <ContentHtml className="panel-body">{props.overviewMessage}</ContentHtml>
        </div>
      </section>
    }

    <section>
      <h3 className="h2">{trans('slides', {}, 'slideshow')}</h3>

      {0 === props.slides.length &&
        <ContentPlaceholder
          size="lg"
          icon="fa fa-image"
          title={trans('no_slide', {}, 'slideshow')}
        />
      }

      {0 !== props.slides.length &&
        <ul className="slides">
          {props.slides.map(slide =>
            <li key={slide.id} className="slide-preview">
              <LinkButton target={`${props.path}/play/${slide.id}`}>
                <img src={asset(slide.content.url)} alt={slide.title} className="img-thumbnail"/>
              </LinkButton>
            </li>
          )}
        </ul>
      }
    </section>

    <Button
      type={LINK_BUTTON}
      className="btn btn-block btn-emphasis"
      icon="fa fa-fw fa-play"
      label={trans('start_slideshow', {}, 'slideshow')}
      target={`${props.path}/play`}
      primary={true}
      disabled={0 === props.slides.length}
    />
  </section>

OverviewComponent.propTypes = {
  path: T.string.isRequired,
  overviewMessage: T.string,
  slides: T.arrayOf(T.shape(
    SlideTypes.propTypes
  )).isRequired
}

const Overview = connect(
  (state) => ({
    path: resourceSelectors.path(state),
    overviewMessage: selectors.overviewMessage(state),
    slides: selectors.slides(state)
  })
)(OverviewComponent)

export {
  Overview
}
