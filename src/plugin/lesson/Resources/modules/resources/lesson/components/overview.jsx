import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {scrollTo} from '#/main/app/dom/scroll'
import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ContentSummary} from '#/main/app/content/components/summary'
import {SearchMinimal} from '#/main/app/content/search/components/minimal'
import {ResourceOverview} from '#/main/core/resource/components/overview'
import {constants as LESSON_NUMBERINGS} from '#/plugin/lesson/resources/lesson/constants'
import {getNumbering} from '#/plugin/lesson/resources/lesson/utils'

const LessonOverview = (props) => {

  function getChapterSummary(chapter) {
    return {
      id: chapter.id,
      type: LINK_BUTTON,
      label: (
        <Fragment>
          {(props.lesson.display.numbering && props.lesson.display.numbering !== LESSON_NUMBERINGS.NUMBERING_NONE && getNumbering(props.lesson.display.numbering, props.tree.children, chapter) ?
            <span className="h-numbering">{getNumbering(props.lesson.display.numbering, props.tree.children, chapter)}</span>
            : ''
          )}
          {chapter.title}
        </Fragment>
      ),
      target: `${props.path}/${chapter.slug}`,
      children: chapter.children ? chapter.children.map(getChapterSummary) : [],
      onClick: () => {
        scrollTo(`#resource-${props.resourceId} > .page-content`)
      }
    }
  }

  const chapters = get(props.tree, 'children', [])

  return (
    <ResourceOverview
      contentText={get(props.lesson, 'display.description')}
      resourceNode={props.resourceNode}
      actions={[
        {
          type: LINK_BUTTON,
          label: trans('start', {}, 'actions'),
          target: `${props.path}/${get(chapters, '[0].slug')}`,
          primary: true,
          disabled: isEmpty(chapters),
          disabledMessages: isEmpty(chapters) ? [trans('start_disabled_empty', {}, 'lesson')]:[]
        }
      ]}
    >
      <SearchMinimal
        size="lg"
        placeholder={trans('lesson_search', {}, 'lesson')}
        search={(searchStr) => {
          props.search(searchStr, props.internalNotes)
          // open search list
          props.history.push(props.path+'/chapters')
        }}
      />

      <section className="resource-parameters mb-3">
        <h3 className="h2">{trans('summary')}</h3>

        <ContentSummary
          className="component-container"
          links={chapters.map(getChapterSummary)}
        />
      </section>
    </ResourceOverview>
  )
}

LessonOverview.propTypes = {
  history: T.shape({
    push: T.func.isRequired
  }).isRequired,
  path: T.string.isRequired,
  resourceId: T.string.isRequired,
  tree: T.object,
  lesson: T.object.isRequired,
  internalNotes: T.bool.isRequired,
  search: T.func.isRequired,
  resourceNode: T.object
}

export {
  LessonOverview
}
