import React from 'react'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl'
import {ResourceEditor} from '#/main/core/resource'

import {selectors as baseSelectors} from '#/plugin/slideshow/resources/slideshow/store/selectors'
import {SlideshowEditorAppearance} from '#/plugin/slideshow/resources/slideshow/editor/components/appearance'
import {SlideshowEditorContent} from '#/plugin/slideshow/resources/slideshow/editor/components/content'

const SlideshowEditor = () => {
  const slideshow = useSelector(baseSelectors.slideshow)

  return (
    <ResourceEditor
      styles={['claroline-distribution-plugin-slideshow-slideshow-resource']}
      defaultPage="content"
      appearancePage={SlideshowEditorAppearance}
      additionalData={() => ({
        resource: slideshow
      })}
      pages={[
        {
          name: 'content',
          title: trans('slides', {}, 'slideshow'),
          help: trans('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'),
          component: SlideshowEditorContent
        }
      ]}
    />
  )
}

export {
  SlideshowEditor
}
