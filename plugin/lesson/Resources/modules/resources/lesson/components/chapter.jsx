import React from 'react'
import {connect} from 'react-redux'
import isEmpty from 'lodash/isEmpty'

import {ContentLoader} from '#/main/app/content/components/loader'
import {ContentTitle} from '#/main/app/content/components/title'

import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {ContentHtml} from '#/main/app/content/components/html'

import {flattenChapters} from '#/plugin/lesson/resources/lesson/utils'
import {LessonCurrent} from '#/plugin/lesson/resources/lesson/components/current'
import {selectors} from '#/plugin/lesson/resources/lesson/store'

const Chapter = props => {
  if (isEmpty(props.chapter)) {
    return (
      <ContentLoader
        size="lg"
        description="Nous chargeons votre chapitre..."
      />
    )
  }

  return (
    <LessonCurrent
      prefix={props.path}
      current={props.chapter}
      all={flattenChapters(props.treeData.children || [])}
    >
      <section className="current-chapter">
        <ContentTitle
          level={1}
          displayLevel={2}
          title={props.chapter.title}
        />

        <div className="panel panel-default">
          <ContentHtml className="panel-body">
            {props.chapter.text ? props.chapter.text : ''}
          </ContentHtml>
        </div>
      </section>
    </LessonCurrent>
  )
}

const ChapterResource = connect(
  state => ({
    path: resourceSelectors.path(state),
    chapter: selectors.chapter(state),
    treeData: selectors.treeData(state)
  })
)(Chapter)

export {
  ChapterResource
}