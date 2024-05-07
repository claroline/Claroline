import React from 'react'

import {Resource} from '#/main/core/resource'

import {SlideshowEditor} from '#/plugin/slideshow/resources/slideshow/editor/components/main'
import {SlideshowOverview} from '#/plugin/slideshow/resources/slideshow/components/overview'
import {SlideshowPlayer} from '#/plugin/slideshow/resources/slideshow/components/player'

const SlideshowResource = props =>
  <Resource
    {...props}
    styles={['claroline-distribution-plugin-slideshow-slideshow-resource']}
    actions={[
      /*{
        name: 'play',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-play',
        label: trans('start', {}, 'actions'),
        target: `${props.path}/play`
      }*/
    ]}/*
    redirect={[
      {from: '/', exact: true, to: '/play', disabled: props.showOverview}
    ]}*/
    editor={SlideshowEditor}
    overviewPage={SlideshowOverview}
    pages={[
      {
        path: '/play/:id?',
        component: SlideshowPlayer
      }
    ]}
  />

export {
  SlideshowResource
}
