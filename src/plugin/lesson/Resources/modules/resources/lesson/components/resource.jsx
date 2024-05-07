import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON,} from '#/main/app/buttons'
import {Resource} from '#/main/core/resource'

import {Chapter} from '#/plugin/lesson/resources/lesson/containers/chapter'
import {LessonOverview} from '#/plugin/lesson/resources/lesson/components/overview'
import {LessonEditor} from '#/plugin/lesson/resources/lesson/editor/components/main'

const LessonResource = (props) =>
  <Resource
    {...omit(props, 'lesson', 'loadChapter', 'downloadLessonPdf')}
    styles={['claroline-distribution-plugin-lesson-lesson-resource']}
    /*actions={[
      {
        name: 'download',
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-file-pdf',
        //displayed: this.props.canExport,
        label: trans('export-pdf', {}, 'actions'),
        group: trans('transfer'),
        callback: () => props.downloadLessonPdf(props.lesson.id)
      }
    ]}*/
    editor={LessonEditor}
    overviewPage={LessonOverview}
    pages={[
      {
        path: '/:slug',
        exact: true,
        onEnter: params => props.loadChapter(props.lesson.id, params.slug),
        component: Chapter
      }
    ]}
  />

LessonResource.propTypes = {
  lesson: T.any.isRequired,
  loadChapter: T.func.isRequired,
  downloadLessonPdf: T.func.isRequired
}

export {
  LessonResource
}
