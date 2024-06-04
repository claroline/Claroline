import React from 'react'
import {PropTypes as T} from 'prop-types'

import {matchPath} from '#/main/app/router'
import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {SearchMinimal} from '#/main/app/content/search/components/minimal'
import {ContentSummary} from '#/main/app/content/components/summary'

import {getNumbering} from '#/plugin/lesson/resources/lesson/utils'

const LessonSummary = props => {
  function getChapterSummary(chapter) {
    return {
      id: chapter.id,
      type: LINK_BUTTON,
      numbering: getNumbering(props.lesson.display.numbering, props.tree.children, chapter),
      label: chapter.title,
      target: `${props.path}/${chapter.slug}`,
      //active: !!matchPath(props.location.pathname, {path: `${props.path}/${chapter.slug}`}),
      additional: [
        {
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-file-pdf',
          displayed: props.canExport,
          label: trans('export-pdf', {}, 'actions'),
          group: trans('transfer'),
          callback: () => props.downloadChapterPdf(props.lesson.id, chapter.id)
        }
      ],
      children: chapter.children ? chapter.children.map(getChapterSummary) : []
    }
  }

  const chapters = props.tree.children || []

  return (
    <>
      <SearchMinimal
        className="mb-3"
        /*size="lg"*/
        placeholder={trans('lesson_search', {}, 'lesson')}
        search={(searchStr) => {
          props.search(searchStr, props.internalNotes)
          // open search list
          props.history.push(props.path+'/chapters')
        }}
      />

      <ContentSummary
        links={chapters.map(getChapterSummary)}
      />
    </>
  )
}

LessonSummary.propTypes = {
  history: T.shape({
    push: T.func.isRequired
  }).isRequired,
  location: T.shape({
    pathname: T.string.isRequired
  }).isRequired,
  path: T.string.isRequired,
  overview: T.bool.isRequired,
  editable: T.bool.isRequired,
  internalNotes: T.bool.isRequired,
  canExport: T.bool.isRequired,
  lesson: T.object.isRequired,
  tree: T.any.isRequired,
  delete: T.func.isRequired,
  downloadChapterPdf: T.func.isRequired,
  search: T.func.isRequired
}

export {
  LessonSummary
}
