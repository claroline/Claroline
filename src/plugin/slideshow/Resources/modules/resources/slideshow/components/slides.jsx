import React from 'react'
import {PropTypes as T} from 'prop-types'

import {asset} from '#/main/app/config/asset'
import {Toolbar} from '#/main/app/action/components/toolbar'

import {Slide as SlideTypes} from '#/plugin/slideshow/resources/slideshow/prop-types'

const Slides = props =>
  <ul className="slides">
    {props.slides.map(slide =>
      <li key={slide.id} className="slide-preview">
        <img src={asset(slide.content)} alt={slide.title} className="img-fluid" />

        {props.actions &&
          <Toolbar
            id={`${slide.id}-btn`}
            className="slide-actions"
            buttonName="btn btn-text-subtitles"
            tooltip="bottom"
            size="sm"
            /*toolbar="more"*/
            actions={props.actions(slide)}
          />
        }
      </li>
    )}
  </ul>

Slides.propTypes = {
  slides: T.arrayOf(T.shape(
    SlideTypes.propTypes
  )),
  actions: T.func
}

export {
  Slides
}
