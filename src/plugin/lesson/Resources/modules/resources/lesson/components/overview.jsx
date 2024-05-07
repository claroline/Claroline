import React from 'react'
import {useSelector} from 'react-redux'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {ResourceOverview, selectors as resourceSelectors} from '#/main/core/resource'
import {selectors} from '#/plugin/lesson/resources/lesson/store'

import {LINK_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'
import {LessonSummary} from '#/plugin/lesson/resources/lesson/containers/summary'
import {PageSection} from '#/main/app/page/components/section'


const LessonOverview = () => {
  const resourcePath = useSelector(resourceSelectors.path)
  const tree = useSelector(selectors.treeData)
  const chapters = get(tree, 'children', [])

  return (
    <ResourceOverview
      actions={[
        {
          name: 'start',
          type: LINK_BUTTON,
          label: trans('start', {}, 'actions'),
          target: `${resourcePath}/${get(chapters, '[0].slug')}`,
          primary: true,
          disabled: isEmpty(chapters)
        }
      ]}
    >
      <PageSection
        size="md"
        className="py-3"
        title={trans('summary')}
      >
        {isEmpty(chapters) ?
          <ContentPlaceholder
            title={trans('no_chapter', {}, 'lesson')}
            size="lg"
          /> :
          <LessonSummary />
        }
      </PageSection>
    </ResourceOverview>
  )
}

export {
  LessonOverview
}
