import React from 'react'
import {useSelector} from 'react-redux'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {ContentSummary} from '#/main/app/content/components/summary'
import {EditorPage} from '#/main/app/editor'
import {selectors as resourceSelectors} from '#/main/core/resource/editor'

import {getNumbering} from '#/plugin/lesson/resources/lesson/utils'

const LessonEditorSummary = () => {
  const resourceEditorPath = useSelector(resourceSelectors.path) + '/content'

  const baseNumbering = useSelector((state) => get(resourceSelectors.resource(state), 'display.numbering'))
  const editedChapters = useSelector((state) => get(resourceSelectors.data(state), 'chapters', []))

  function getChapterSummary(chapter) {
    let numbering = getNumbering(baseNumbering, editedChapters, chapter)
    if (numbering.length > 0) {
      numbering = `${numbering}. `
    }

    return {
      id: chapter.id,
      type: LINK_BUTTON,
      label: numbering + chapter.title,
      target: `${resourceEditorPath}/${chapter.slug}`,
      additional: [
        {
          name: 'add',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-plus',
          label: trans('new_subchapter', {}, 'lesson'),
          target: `${resourceEditorPath}/${chapter.slug}/subchapter`,
          group: trans('management')
        }, /*{
          name: 'edit',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-pencil',
          label: trans('edit', {}, 'actions'),
          target: `${resourceEditorPath}/${chapter.slug}/edit`,
          group: trans('management')
        }, */{
          name: 'copy',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-clone',
          label: trans('copy', {}, 'actions'),
          target: `${resourceEditorPath}/${chapter.slug}/copy`,
          group: trans('management')
        }, {
          name: 'delete',
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-trash',
          label: trans('delete', {}, 'actions'),
          callback: () => props.delete(props.lesson.id, chapter.slug, chapter.title, props.history, props.path),
          dangerous: true,
          group: trans('management')
        }
      ],
      children: chapter.children ? chapter.children.map(getChapterSummary) : []
    }
  }

  return (
    <EditorPage
      title={trans('content')}
      help={trans('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.')}
    >
      <ContentSummary
        links={editedChapters.map(getChapterSummary)}
      />
    </EditorPage>
  )
}


export {
  LessonEditorSummary
}
