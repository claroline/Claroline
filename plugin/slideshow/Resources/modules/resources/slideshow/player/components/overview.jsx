import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {HtmlText} from '#/main/core/layout/components/html-text'
import {EmptyPlaceholder} from '#/main/core/layout/components/placeholder'

import {selectors} from '#/plugin/slideshow/resources/slideshow/player/store'
import {Slides} from '#/plugin/slideshow/resources/slideshow/components/slides'
import {Slide as SlideTypes} from '#/plugin/slideshow/resources/slideshow/prop-types'

// TODO : merge with standard overview when UserProgression will be implemented for slideshow

const OverviewComponent = props =>
  <section className="resource-section resource-overview">
    <h2 className="sr-only">{trans('resource_overview', {}, 'resource')}</h2>

    {props.overviewMessage &&
      <section className="resource-info">
        <h3 className="h2">{trans('resource_overview_info', {}, 'resource')}</h3>

        <div className="panel panel-default">
          <HtmlText className="panel-body">{props.overviewMessage}</HtmlText>
        </div>
      </section>
    }

    <section className="">
      <h3 className="h2">{trans('slides', {}, 'slideshow')}</h3>

      {0 === props.slides.length &&
        <EmptyPlaceholder
          size="lg"
          icon="fa fa-image"
          title={trans('no_slide', {}, 'slideshow')}
        />
      }

      {0 !== props.slides.length &&
        <Slides
          slides={props.slides}
        />
      }
    </section>
  </section>

OverviewComponent.propTypes = {
  overviewMessage: T.string,
  slides: T.arrayOf(T.shape(
    SlideTypes.propTypes
  )).isRequired
}

const Overview = connect(
  (state) => ({
    overviewMessage: selectors.overviewMessage(state),
    slides: selectors.slides(state)
  })
)(OverviewComponent)

export {
  Overview
}
