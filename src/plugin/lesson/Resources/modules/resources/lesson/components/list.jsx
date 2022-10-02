import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {asset} from '#/main/app/config/asset'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'
import {DataCard} from '#/main/app/data/components/card'

import {selectors} from '#/plugin/lesson/resources/lesson/store/selectors'

const ChapterCard = (props) =>
  <DataCard
    {...props}
    id={props.data.id}
    poster={props.data.poster ? asset(props.data.poster) : null}
    title={props.data.title}
    contentText={props.data.text}
  />

const ChapterList = (props) =>
  <ListData
    name={selectors.LIST_NAME}
    fetch={{
      url: ['apiv2_lesson_chapter_list', {lessonId: props.lesson.id}],
      autoload: true
    }}
    primaryAction={(row) => ({
      type: LINK_BUTTON,
      target: props.path + '/' + row.slug
    })}
    definition={[
      {
        name: 'title',
        type: 'string',
        label: trans('title'),
        displayed: true,
        filterable: false,
        primary: true
      }, {
        name: 'text',
        type: 'html',
        label: trans('text'),
        displayed: true,
        filterable: false
      }, {
        name: 'internalNote',
        type: 'html',
        label: trans('internal_note'),
        displayed: props.internalNotes,
        displayable: props.internalNotes,
        filterable: false
      }, {
        name: 'content',
        label: trans('content'),
        displayable: false,
        sortable: false,
        filterable: !props.internalNotes
      }, {
        name: 'contentAndNote',
        label: trans('content'),
        displayable: false,
        sortable: false,
        filterable: props.internalNotes
      }
    ]}
    card={ChapterCard}
  />

ChapterList.propTypes = {
  path: T.string.isRequired,
  lesson: T.shape({
    id: T.string
  }).isRequired,
  internalNotes: T.bool
}

export {
  ChapterList
}
