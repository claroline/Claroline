import React from 'react'
import {useSelector} from 'react-redux'
import classes from 'classnames'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {selectors as resourceSelectors} from '#/main/core/resource'
import {selectors} from '#/plugin/lesson/resources/lesson/store'

import {trans} from '#/main/app/intl'
import {PageSection} from '#/main/app/page/components/section'
import {Html} from '#/main/app/components/html'
import {ContentSummary} from '#/main/app/content/components/summary'
import {LINK_BUTTON} from '#/main/app/buttons'
import {getNumbering} from '#/plugin/lesson/resources/lesson/utils'
import {PageContent} from '#/main/app/page'
import {EmptyState} from '#/main/app/components/empty-state'
import {selectors as contextSelectors} from '#/main/app/context'
import {hasPermission} from '#/main/app/security'

const LessonPlayerOverview = () => {
  const contextPath = useSelector(contextSelectors.path)
  const resourcePath = useSelector(resourceSelectors.path)
  const resourceNode = useSelector(resourceSelectors.resourceNode)
  const showHeader = useSelector(resourceSelectors.showHeader)
  const embedded = useSelector(resourceSelectors.embedded)
  const description = get(resourceNode, 'meta.descriptionHtml', null)
  const canAdd = useSelector((state) => hasPermission('edit', resourceSelectors.resourceNode(state)))

  const numbering = useSelector(selectors.numbering)
  const tree = useSelector(selectors.tree)
  const chapters = get(tree, 'children', [])

  function getChapterSummary(chapter) {
    return {
      id: chapter.id,
      type: LINK_BUTTON,
      numbering: getNumbering(numbering, chapters, chapter),
      label: chapter.title,
      target: `${resourcePath}/${chapter.slug}`,
      children: chapter.children ? chapter.children.map(getChapterSummary) : []
    }
  }

  return (
    <PageContent className={classes('d-flex flex-column', {
      'mx-n4': embedded
    })} poster={get(resourceNode, 'poster')}>
      {description &&
        <PageSection className={classes({
          'pt-5': !embedded || showHeader
        })}>
          <Html className="content-text mb-5">{description}</Html>
        </PageSection>
      }

      {isEmpty(chapters) &&
        <EmptyState
          className="px-4 pb-5"
          icon="fa fa-file-lines"
          title={trans('no_page', {}, 'lesson')}
          description={trans(canAdd ? 'no_page_manager_help' : 'no_page_help', {}, 'lesson')}
          primaryAction={{
            type: LINK_BUTTON,
            label: trans('add_page', {}, 'actions'),
            target: `${resourcePath}/new`,
            displayed: canAdd
          }}
          secondaryAction={{
            type: LINK_BUTTON,
            icon: 'fa fa-arrow-left',
            label: trans('back_home', {}, 'actions'),
            target: contextPath,
            displayed: !embedded
          }}
        />
      }

      {!isEmpty(chapters) &&
        <PageSection
          title={trans('summary')}
          className={classes({
            'mt-5': !description && (!embedded || showHeader)
          })}
        >
          <ContentSummary
            className="mb-5"
            links={chapters.map(getChapterSummary)}
          />
        </PageSection>
      }
    </PageContent>
  )
}

export {
  LessonPlayerOverview
}
