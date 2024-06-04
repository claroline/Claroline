import React from 'react'
import {useSelector} from 'react-redux'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl'
import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {ContentSummary} from '#/main/app/content/components/summary'
import {EditorPage} from '#/main/app/editor'
import {selectors as resourceSelectors} from '#/main/core/resource/editor'

import {getNumbering} from '#/plugin/lesson/resources/lesson/utils'
import {selectors} from '#/plugin/lesson/resources/lesson/editor/store'

const LessonEditorSummary = () => {
  const resourceEditorPath = useSelector(resourceSelectors.path) + '/content'

  const baseNumbering = useSelector(selectors.numbering)
  const editedChapters = useSelector(selectors.chapters)

  function getChapterSummary(chapter) {
    return {
      id: chapter.id,
      type: LINK_BUTTON,
      numbering: getNumbering(baseNumbering, editedChapters, chapter),
      label: chapter.title,
      target: `${resourceEditorPath}/${chapter.slug}`,
      additional: [
        {
          name: 'add',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-plus',
          label: trans('new_subchapter', {}, 'lesson'),
          target: `${resourceEditorPath}/${chapter.slug}/subchapter`
        }, {
          name: 'copy',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-clone',
          label: trans('copy', {}, 'actions'),
          target: `${resourceEditorPath}/${chapter.slug}/copy`
        }, {
          name: 'delete',
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-trash',
          label: trans('delete', {}, 'actions'),
          callback: () => props.delete(props.lesson.id, chapter.slug, chapter.title, props.history, props.path),
          dangerous: true
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

      <Button
        type={CALLBACK_BUTTON}
        className={classes('btn btn-primary w-100 mt-3', {
          'btn-wave': isEmpty(editedChapters)
        })}
        label={trans('Ajouter une section')}
        size="lg"
        callback={() => true}
      />
    </EditorPage>
  )
}


export {
  LessonEditorSummary
}
