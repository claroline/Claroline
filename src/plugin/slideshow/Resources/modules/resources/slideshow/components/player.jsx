import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import get from 'lodash/get'
import tinycolor from 'tinycolor2'
import Carousel from 'react-bootstrap/Carousel'

import {asset} from '#/main/app/config/asset'
import {trans} from '#/main/app/intl/translation'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'
import {ResourcePage} from '#/main/core/resource'

import {selectors} from '#/plugin/slideshow/resources/slideshow/store'
import {Slide as SlideTypes} from '#/plugin/slideshow/resources/slideshow/prop-types'

const PlayerComponent = props => {
  if (0 === props.slides.length) {
    return (
      <ContentPlaceholder
        size="lg"
        icon="fa fa-image"
        title={trans('no_slide', {}, 'slideshow')}
      />
    )
  }

  const activeSlide = get(props.match, 'params.id')
  let activeIndex = props.slides.findIndex(slide => slide.id === activeSlide)
  if (-1 === activeIndex) {
    activeIndex = 0
  }

  return (
    <ResourcePage>
      <Carousel
        defaultActiveIndex={activeIndex}
        interval={props.autoPlay ? props.interval : undefined}
        controls={props.showControls}
        indicators={props.showControls}

        //prevIcon={<span className="fa fa-chevron-left" />}
        prevLabel={trans('previous')}
        //nextIcon={<span className="fa fa-chevron-right" />}
        nextLabel={trans('next')}
      >
        {props.slides.map(slide => {
          let color
          if (get(slide, 'display.color')) {
            color = tinycolor(slide.display.color)
          }

          return (
            <Carousel.Item
              key={slide.id}
              style={color ? {
                backgroundColor: color.toRgbString()
              } : undefined}
            >
              <img src={asset(slide.content)} alt={slide.title} className="d-block m-auto" />

              {(slide.meta.title || slide.meta.description) &&
                <Carousel.Caption>
                  {slide.meta.title &&
                    <h3>{slide.meta.title}</h3>
                  }

                  {slide.meta.description &&
                    <p>{slide.meta.description}</p>
                  }
                </Carousel.Caption>
              }
            </Carousel.Item>
          )
        })}
      </Carousel>
    </ResourcePage>
  )
}

PlayerComponent.propTypes = {
  autoPlay: T.bool.isRequired,
  interval: T.number.isRequired,
  showControls: T.bool,
  slides: T.arrayOf(T.shape(
    SlideTypes.propTypes
  )).isRequired,
  // from router
  match: T.shape({
    params: T.shape({
      id: T.string
    }).isRequired
  }).isRequired
}

const SlideshowPlayer = connect(
  (state) => ({
    autoPlay: selectors.autoPlay(state),
    interval: selectors.interval(state),
    showControls: selectors.showControls(state),
    slides: selectors.slides(state)
  })
)(PlayerComponent)

export {
  SlideshowPlayer
}
