import React from 'react'
import {useSelector} from 'react-redux'

import {asset} from '#/main/app/config/asset'
import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {LinkButton} from '#/main/app/buttons/link/components/button'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'
import {PageSection} from '#/main/app/page/components/section'

import {ResourceOverview} from '#/main/core/resource'
import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {selectors} from '#/plugin/slideshow/resources/slideshow/store'

const SlideshowOverview = () => {
  const path = useSelector(resourceSelectors.path)
  const slides = useSelector(selectors.slides)

  return (
    <ResourceOverview
      actions={[
        {
          name: 'start',
          type: LINK_BUTTON,
          label: trans('start', {}, 'actions'),
          target: `${path}/play`,
          disabled: 0 === slides.length,
          primary: true
        }
      ]}
    >
      <PageSection size="md">
        {0 === slides.length ?
          <ContentPlaceholder
            size="lg"
            icon="fa fa-image"
            title={trans('no_slide', {}, 'slideshow')}
          /> :
          <ul className="slides">
            {slides.map(slide =>
              <li key={slide.id} className="slide-preview">
                <LinkButton target={`${path}/play/${slide.id}`}>
                  <img src={asset(slide.content)} alt={slide.title} className="img-fluid" />
                </LinkButton>
              </li>
            )}
          </ul>
        }
      </PageSection>
    </ResourceOverview>
  )
}

export {
  SlideshowOverview
}
