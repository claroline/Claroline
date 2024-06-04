import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector} from 'react-redux'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {EditorPage} from '#/main/app/editor'
import {selectors as editorSelectors} from '#/main/core/resource/editor'

import {selectors} from '#/plugin/lesson/resources/lesson/editor/store'
import {getNumbering} from '#/plugin/lesson/resources/lesson/utils'
import {LINK_BUTTON} from '#/main/app/buttons'

const LessonEditorChapter = (props) => {
  const resourceEditorPath = useSelector(editorSelectors.path)
  const hasInternalNotes = useSelector(selectors.hasInternalNotes)
  const hasCustomNumbering = useSelector(selectors.hasCustomNumbering)
  const numbering = useSelector(selectors.numbering)

  const allChapters = useSelector(selectors.chapters)
  let chapterIndex
  let chapter
  if (get(props.match, 'params.slug')) {
    chapterIndex = allChapters.findIndex(c => c.slug === get(props.match, 'params.slug'))
    if (-1 !== chapterIndex) {
      chapter = allChapters[chapterIndex]
    } else {
      chapterIndex = 0
    }
  }

  const chapterNumbering = getNumbering(numbering, allChapters, chapter)

  return (
    <EditorPage
      title={
        <>
          {chapterNumbering &&
            <span className="h-numbering">{chapterNumbering}</span>
          }

          {get(chapter, 'title') || trans('section', {number: chapterIndex + 1}, 'lesson')}
        </>
      }
      dataPart={`chapters[${chapterIndex}]`}
      actions={[
        {
          name: 'summary',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-list',
          label: trans('open-summary', {}, 'actions'),
          target: resourceEditorPath+'/content',
          exact: true
        }
      ]}
      definition={[
        {
          name: 'general',
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'poster',
              label: trans('poster'),
              type: 'poster',
              hideLabel: true
            }, {
              name: 'title',
              label: trans('title'),
              type: 'string',
              required: true,
              autoFocus: true
            }, {
              name: 'text',
              type: 'html',
              label: trans('text'),
              required: true,
              options: {
                //workspace: props.workspace,
                minRows: 10
              }
            }
          ]
        }, {
          title: trans('further_information'),
          subtitle: trans('further_information_help'),
          primary: true,
          fields: [
            {
              name: 'customNumbering',
              type: 'string',
              label: trans('chapter_numbering', {}, 'lesson'),
              displayed: hasCustomNumbering
            }, {
              name: 'meta.description',
              label: trans('Description courte'),
              help: trans('Décrivez succintement votre section (La description courte est affichée dans le sommaire).'),
              type: 'string',
              options: {
                long: true,
                minRows: 2
              }
            }, {
              name: '_enableInternalNote',
              type: 'boolean',
              label: trans('Ajouter une note interne'),
              displayed: hasInternalNotes,
              help: trans('internal_note_visibility_help', {}, 'lesson'),
              calculated: (chapter) => chapter._enableInternalNote || chapter.internalNote,
              linked: [
                {
                  name: 'internalNote',
                  type: 'html',
                  label: trans('text'),
                  required: true,
                  displayed: (chapter) => chapter._enableInternalNote || chapter.internalNote,
                  options: {
                    workspace: props.workspace,
                    minRows: 10
                  }
                }
              ]
            }
          ]
        }
      ]}
    />
  )
}

LessonEditorChapter.propTypes = {
  match: T.shape({
    params: T.shape({
      slug: T.string.isRequired
    }).isRequired
  }).isRequired
}

export {
  LessonEditorChapter
}
