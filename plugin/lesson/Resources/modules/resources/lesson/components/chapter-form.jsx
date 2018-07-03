import React from 'react'
import {connect} from 'react-redux'
import {withRouter} from '#/main/app/router'
import ButtonToolbar from 'react-bootstrap/lib/ButtonToolbar'
import {FormContainer} from '#/main/core/data/form/containers/form.jsx'
import {actions as formActions} from '#/main/core/data/form/actions'
import {select as formSelect} from '#/main/core/data/form/selectors'
import {Button} from '#/main/app/action/components/button'
import {trans} from '#/main/core/translation'
import {constants} from '#/plugin/lesson/resources/lesson/constants'
import {buildParentChapterChoices} from '#/plugin/lesson/resources/lesson/components/tree/utils'
import {actions as lessonAction} from '#/plugin/lesson/resources/lesson/store/'

const ChapterFormComponent = props =>
  <FormContainer
    name={constants.CHAPTER_EDIT_FORM_NAME}
    sections={[
      {
        id: 'chapter',
        title: 'Chapter form',
        primary: true,
        fields: [
          {
            name: 'title',
            type: 'string',
            label: trans('title'),
            required: true
          },
          {
            name: 'move',
            type: 'boolean',
            label: trans('move_chapter', {}, 'icap_lesson'),
            displayed: !props.isNew
          },
          {
            name: 'parentSlug',
            type: 'choice',
            label: trans('move_destination', {}, 'icap_lesson'),
            required: true,
            displayed: props.isNew || props.chapterWillBeMoved,
            options: {
              multiple: false,
              condensed: true,
              choices: buildParentChapterChoices(props.tree, props.chapter)
            },
            onChange: value => props.positionChange(value)
          },
          {
            name: 'position',
            type: 'choice',
            label: trans('move_relation', {}, 'icap_lesson'),
            required: false,
            displayed: (props.isNew || props.chapterWillBeMoved) && !props.isRootSelected,
            disabled: false,
            options: {
              condensed: false,
              multiple: false,
              choices: {
                subchapter: trans('subchapter', {}, 'icap_lesson'),
                sibling: trans('sibling', {}, 'icap_lesson')
              }
            }
          },
          {
            name: 'order.subchapter',
            type: 'choice',
            label: trans('options'),
            required: false,
            displayed: (props.isNew ||props.chapterWillBeMoved) && props.isSubchapterSelected,
            options: {
              condensed: false,
              multiple: false,
              choices: {
                first: trans('first', {}, 'icap_lesson'),
                last: trans('last', {}, 'icap_lesson')
              }
            }
          },
          {
            name: 'order.sibling',
            type: 'choice',
            label: trans('options'),
            required: false,
            displayed: (props.isNew || props.chapterWillBeMoved) && !props.isSubchapterSelected,
            options: {
              condensed: false,
              multiple: false,
              choices: {
                before: trans('before', {}, 'icap_lesson'),
                after: trans('after', {}, 'icap_lesson')
              }
            }
          },
          {
            name: 'text',
            type: 'html',
            label: trans('text'),
            required: true
          }
        ]
      }
    ]}
  >
    <ButtonToolbar>
      <Button
        disabled={!props.saveEnabled}
        primary={true}
        label={trans(props.isNew ? 'create' : 'save')}
        type="callback"
        className="btn"
        callback={() => {
          props.save(props.isNew, props.lesson.id, props.isNew ? props.parentSlug : props.chapter.slug, props.history)
        }}
      />
      <Button
        label={trans('cancel')}
        title={trans('cancel')}
        type="callback"
        className="btn"
        callback={() => {props.cancel(props.history, props.chapter.slug || props.lesson.firstChapterSlug || '')}}
      />
    </ButtonToolbar>

  </FormContainer>

const ChapterForm = withRouter(connect(
  state => ({
    lesson: state.lesson,
    chapter: state.chapter,
    tree: state.tree.data,
    mode: state.mode,
    saveEnabled: formSelect.saveEnabled(formSelect.form(state, constants.CHAPTER_EDIT_FORM_NAME)),
    isNew: formSelect.isNew(formSelect.form(state, constants.CHAPTER_EDIT_FORM_NAME)),
    parentSlug: formSelect.data(formSelect.form(state, constants.CHAPTER_EDIT_FORM_NAME)).parentSlug,
    isRootSelected: formSelect.data(formSelect.form(state, constants.CHAPTER_EDIT_FORM_NAME)).parentSlug === state.tree.data.slug,
    isSubchapterSelected: formSelect.data(formSelect.form(state, constants.CHAPTER_EDIT_FORM_NAME)).position === 'subchapter',
    chapterWillBeMoved: !!formSelect.data(formSelect.form(state, constants.CHAPTER_EDIT_FORM_NAME)).move
  }),
  dispatch => ({
    save: (isNew, lessonId, slug, history) => {
      dispatch(formActions.saveForm(constants.CHAPTER_EDIT_FORM_NAME, [isNew ? 'apiv2_lesson_chapter_create' : 'apiv2_lesson_chapter_update', {lessonId, slug}]))
        .then(
          (success) => {
            history.push('/' + success['slug'])
          }
        )
    },
    cancel: (history, slug) => {
      dispatch(formActions.cancelChanges(constants.CHAPTER_EDIT_FORM_NAME))
      history.push('/' + slug)
    },
    positionChange: value => {
      dispatch(lessonAction.positionChange(value))
    }
  })
)(ChapterFormComponent))

export {
  ChapterForm
}
