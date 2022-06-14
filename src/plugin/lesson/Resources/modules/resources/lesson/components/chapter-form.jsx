import React from 'react'
import {connect} from 'react-redux'
import {withRouter} from '#/main/app/router'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {buildParentChapterChoices} from '#/plugin/lesson/resources/lesson/utils'
import {actions as lessonActions, selectors} from '#/plugin/lesson/resources/lesson/store'

const ChapterFormComponent = props =>
  <FormData
    name={selectors.CHAPTER_EDIT_FORM_NAME}
    buttons={true}
    save={{
      type: CALLBACK_BUTTON,
      callback: () => props.save(selectors.CHAPTER_EDIT_FORM_NAME, !props.isNew ?
        ['apiv2_lesson_chapter_update', {lessonId: props.lesson.id, slug: props.slug}] :
        ['apiv2_lesson_chapter_create', {lessonId: props.lesson.id, slug: props.parentSlug}]
      ).then((response) => props.history.push(props.path+'/'+response.slug))
    }}
    cancel={{
      type: LINK_BUTTON,
      target: props.path,
      exact: true
    }}
    sections={[
      {
        id: 'chapter',
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'title',
            type: 'string',
            label: trans('title'),
            required: true
          }, {
            name: 'move',
            type: 'boolean',
            label: trans('move_chapter', {}, 'lesson'),
            displayed: !props.isNew
          }, {
            name: 'parentSlug',
            type: 'choice',
            label: trans('move_destination', {}, 'lesson'),
            required: true,
            displayed: props.isNew || props.chapterWillBeMoved,
            options: {
              multiple: false,
              condensed: true,
              choices: buildParentChapterChoices(props.tree, props.chapter)
            },
            onChange: value => props.positionChange(value)
          }, {
            name: 'position',
            type: 'choice',
            label: trans('move_relation', {}, 'lesson'),
            required: false,
            displayed: props.hasParentSlug && (props.isNew || props.chapterWillBeMoved) && !props.isRootSelected,
            disabled: false,
            options: {
              condensed: false,
              multiple: false,
              choices: {
                subchapter: trans('subchapter', {}, 'lesson'),
                sibling: trans('sibling', {}, 'lesson')
              }
            }
          }, {
            name: 'order.subchapter',
            type: 'choice',
            label: trans('options'),
            required: false,
            displayed: props.hasParentSlug && (props.isNew ||props.chapterWillBeMoved) && props.isSubchapterSelected,
            options: {
              condensed: false,
              multiple: false,
              choices: {
                first: trans('first'),
                last: trans('last')
              }
            }
          }, {
            name: 'order.sibling',
            type: 'choice',
            label: trans('options'),
            required: false,
            displayed: props.hasParentSlug && (props.isNew || props.chapterWillBeMoved) && props.isSiblingSelected,
            options: {
              condensed: false,
              multiple: false,
              choices: {
                before: trans('before'),
                after: trans('after')
              }
            }
          }, {
            name: 'text',
            type: 'html',
            label: trans('text'),
            required: true,
            options: {
              workspace: props.workspace,
              minRows: 10
            }
          }
        ]
      }, {
        icon: 'fa fa-fw fa-desktop',
        title: trans('display_parameters'),
        fields: [
          {
            name: 'poster',
            type: 'image',
            label: trans('poster')
          }
        ]
      }, {
        icon: 'fa fa-fw fa-sticky-note',
        title: trans('internal_note'),
        help: trans('internal_note_visibility_help', {}, 'lesson'),
        displayed: props.internalNotes,
        fields: [
          {
            name: 'internalNote',
            type: 'html',
            label: trans('text'),
            options: {
              workspace: props.workspace,
              minRows: 10
            }
          }
        ]
      }
    ]}
  />

const ChapterForm = withRouter(connect(
  state => ({
    path: resourceSelectors.path(state),
    workspace: resourceSelectors.workspace(state),
    lesson: selectors.lesson(state),
    chapter: selectors.chapter(state),
    tree: selectors.treeData(state),
    internalNotes: hasPermission('view_internal_notes', resourceSelectors.resourceNode(state)),
    isNew: formSelectors.isNew(formSelectors.form(state, selectors.CHAPTER_EDIT_FORM_NAME)),
    slug: formSelectors.data(formSelectors.form(state, selectors.CHAPTER_EDIT_FORM_NAME)).slug || null,
    parentSlug: formSelectors.data(formSelectors.form(state, selectors.CHAPTER_EDIT_FORM_NAME)).parentSlug || null,
    hasParentSlug: !!formSelectors.data(formSelectors.form(state, selectors.CHAPTER_EDIT_FORM_NAME)).parentSlug,
    isRootSelected: formSelectors.data(formSelectors.form(state, selectors.CHAPTER_EDIT_FORM_NAME)).parentSlug === selectors.treeData(state).slug,
    isSubchapterSelected: formSelectors.data(formSelectors.form(state, selectors.CHAPTER_EDIT_FORM_NAME)).position === 'subchapter',
    isSiblingSelected: formSelectors.data(formSelectors.form(state, selectors.CHAPTER_EDIT_FORM_NAME)).position === 'sibling',
    chapterWillBeMoved: !!formSelectors.data(formSelectors.form(state, selectors.CHAPTER_EDIT_FORM_NAME)).move
  }),
  dispatch => ({
    save(formName, target) {
      return dispatch(formActions.save(formName, target))
    },
    positionChange: value => {
      dispatch(lessonActions.positionChange(value))
    }
  })
)(ChapterFormComponent))

export {
  ChapterForm
}
