import React, {Fragment} from 'react'
import {connect} from 'react-redux'
import isEmpty from 'lodash/isEmpty'

import {asset} from '#/main/app/config/asset'
import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {ContentLoader} from '#/main/app/content/components/loader'
import {ContentTitle} from '#/main/app/content/components/title'
import {ContentHtml} from '#/main/app/content/components/html'
import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {flattenChapters} from '#/plugin/lesson/resources/lesson/utils'
import {LessonCurrent} from '#/plugin/lesson/resources/lesson/components/current'
import {selectors} from '#/plugin/lesson/resources/lesson/store'

const Chapter = props => {
  if (isEmpty(props.chapter)) {
    return (
      <ContentLoader
        className="row"
        size="lg"
        description={trans('chapter_loading', {}, 'lesson')}
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
        {props.chapter.poster &&
          <img className="chapter-poster img-responsive" alt={props.chapter.title} src={asset(props.chapter.poster.url)} />
        }

        <ContentTitle
          className="chapter-title"
          level={1}
          displayLevel={2}
          title={props.chapter.title}
        />

        <div className="panel panel-default">
          <ContentHtml className="panel-body">
            {props.chapter.text ? props.chapter.text : ''}
          </ContentHtml>
        </div>

        {props.internalNotes && props.chapter.internalNote &&
          <Fragment>
            <ContentTitle
              level={2}
              displayLevel={4}
              title={trans('internal_note')}
            />

            <ContentHtml className="well">
              {props.chapter.internalNote}
            </ContentHtml>
          </Fragment>
        }
      </section>
    </LessonCurrent>
  )
}

const ChapterResource = connect(
  state => ({
    path: resourceSelectors.path(state),
    chapter: selectors.chapter(state),
    internalNotes: hasPermission('view_internal_notes', resourceSelectors.resourceNode(state)),
    treeData: selectors.treeData(state)
  })
)(Chapter)

export {
  ChapterResource
}