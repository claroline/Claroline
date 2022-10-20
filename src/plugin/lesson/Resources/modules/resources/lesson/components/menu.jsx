import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {matchPath} from '#/main/app/router'
import {trans} from '#/main/app/intl/translation'
import {scrollTo} from '#/main/app/dom/scroll'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {MenuSection} from '#/main/app/layout/menu/components/section'
import {SearchMinimal} from '#/main/app/content/search/components/minimal'
import {ContentSummary} from '#/main/app/content/components/summary'

const LessonMenu = props => {
  function getChapterSummary(chapter) {
    return {
      type: LINK_BUTTON,
      label: chapter.title,
      target: `${props.path}/${chapter.slug}`,
      onClick: props.autoClose,
      active: !!matchPath(props.location.pathname, {path: `${props.path}/${chapter.slug}`}),
      additional: [
        {
          name: 'edit',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-pencil',
          label: trans('edit', {}, 'actions'),
          target: `${props.path}/${chapter.slug}/edit`,
          onClick: props.autoClose,
          displayed: props.editable,
          group: trans('management')
        }, {
          name: 'copy',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-clone',
          label: trans('copy', {}, 'actions'),
          target: `${props.path}/${chapter.slug}/copy`,
          onClick: props.autoClose,
          displayed: props.editable,
          group: trans('management')
        }, {
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-file-pdf',
          displayed: props.canExport,
          label: trans('export-pdf', {}, 'actions'),
          group: trans('transfer'),
          onClick: props.autoClose,
          callback: () => props.downloadChapterPdf(props.lesson.id, chapter.id)
        }, {
          name: 'delete',
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-trash-o',
          label: trans('delete', {}, 'actions'),
          callback: () => props.delete(props.lesson.id, chapter.slug, chapter.title, props.history, props.path),
          onClick: (e) => {
            props.autoClose(e)
            scrollTo('.main-page-content')
          },
          displayed: props.editable,
          dangerous: true,
          group: trans('management')
        }
      ],
      children: chapter.children ? chapter.children.map(getChapterSummary) : []
    }
  }

  const chapters = props.tree.children || []

  let baseLinks = []
  if (props.overview) {
    baseLinks = [{
      type: LINK_BUTTON,
      icon: 'fa fa-fw fa-home',
      label: trans('home'),
      target: props.path,
      exact: true,
      onClick: (e) => {
        props.autoClose(e)
        scrollTo('.main-page-content')
      }
    }]
  }

  return (
    <MenuSection
      {...omit(props, 'path')}
      title={trans('icap_lesson', {}, 'resource')}
    >
      <SearchMinimal
        className="app-menu-search"
        placeholder={trans('lesson_search', {}, 'lesson')}
        search={(searchStr) => {
          props.search(searchStr, props.internalNotes)
          // open search list
          props.history.push(props.path+'/chapters')

          props.autoClose()
        }}
      />

      <ContentSummary
        links={baseLinks.concat(chapters.map(getChapterSummary), [{
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-plus',
          label: trans('chapter_creation', {}, 'lesson'),
          target: `${props.path}/new`,
          displayed: props.editable
        }])}
      />
    </MenuSection>
  )
}

LessonMenu.propTypes = {
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
  search: T.func.isRequired,

  // from menu
  opened: T.bool.isRequired,
  toggle: T.func.isRequired,
  autoClose: T.func.isRequired
}

export {
  LessonMenu
}