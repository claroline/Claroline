import React from 'react'
import {useDispatch, useSelector} from 'react-redux'
import {useHistory} from 'react-router-dom'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {PageContent} from '#/main/app/page'
import {FormData, selectors as formSelectors, actions as formActions} from '#/main/app/content/form'

import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {actions, selectors} from '#/plugin/lesson/resources/lesson/store'

const ChapterForm = () => {
  const history = useHistory()
  const dispatch = useDispatch()

  const path = useSelector(resourceSelectors.path)
  const workspace = useSelector(resourceSelectors.workspace)
  const embedded = useSelector(resourceSelectors.embedded)
  const lesson = useSelector(selectors.lesson)
  const placeholders = useSelector(selectors.placeholders)
  const internalNotes = useSelector((state) => hasPermission('view_internal_notes', resourceSelectors.resourceNode(state)))

  const isNew = useSelector((state) => formSelectors.isNew(formSelectors.form(state, selectors.CHAPTER_FORM_NAME)))
  const formData = useSelector((state) => formSelectors.data(formSelectors.form(state, selectors.CHAPTER_FORM_NAME)))

  return (
    <PageContent className={classes('d-flex flex-column', {
      'mx-n4': embedded
    })}>
      <FormData
        className="my-5 px-4 flex-fill"
        level={3}
        displayLevel={5}
        name={selectors.CHAPTER_FORM_NAME}
        buttons={true}
        target={!isNew ? ['apiv2_lesson_chapter_update', {
          lessonId: lesson.id,
          slug: formData.slug
        }] : ['apiv2_lesson_chapter_create', {
          lessonId: lesson.id,
          parentSlug: formData.parentSlug
        }]}
        onSave={(formPage) => {
          if (isNew) {
            dispatch(actions.addPage(formPage))
          } else {
            dispatch(actions.updatePage(formPage))
          }

          history.push(path+'/'+formPage.slug)
        }}
        definition={[
          {
            title: trans('general'),
            primary: true,
            fields: [
              {
                name: 'poster',
                type: 'poster',
                label: trans('poster'),
                hideLabel: true
              }, {
                name: 'title',
                type: 'string',
                required: true,
                label: trans('title')
              }, {
                name: 'contentRaw',
                type: 'html',
                label: trans('content'),
                recommended: true,
                options: {
                  workspace: workspace,
                  minRows: 10,
                  config: {
                    plugins: ['placeholders'],
                    placeholders: placeholders
                  }
                }
              }, {
                name: 'meta.published',
                label: trans('publish_chapter', {}, 'lesson'),
                type: 'boolean',
                help: trans('publish_chapter_help', {}, 'lesson')
              }, {
                name: 'tags',
                label: trans('tags'),
                type: 'tag'
              }, {
                name: '_internalNote',
                type: 'boolean',
                label: trans('add_internal_note', {}, 'lesson'),
                help: trans('internal_note_visibility_help', {}, 'lesson'),
                displayed: internalNotes,
                calculated: (pageData) => !isEmpty(pageData.internalNote) || pageData._internalNote,
                onChange: (enabled) => {
                  if (!enabled) {
                    dispatch(formActions.updateProp(selectors.CHAPTER_FORM_NAME, 'internalNote', null))
                  }
                },
                linked: [
                  {
                    name: 'internalNote',
                    type: 'html',
                    label: trans('internal_note', {}, 'lesson'),
                    displayed: (pageData) => internalNotes  && (!isEmpty(pageData.internalNote) || pageData._internalNote),
                    options: {
                      workspace: workspace,
                      minRows: 10
                    }
                  }
                ]
              }
            ]
          }
        ]}
      />
    </PageContent>
  )
}

export {
  ChapterForm
}
