import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {asset} from '#/main/app/config/asset'
import {trans} from '#/main/app/intl/translation'
import {ContentLoader} from '#/main/app/content/components/loader'
import {ContentTitle} from '#/main/app/content/components/title'
import {ContentHtml} from '#/main/app/content/components/html'

import {Chapter as ChapterTypes} from '#/plugin/lesson/resources/lesson/prop-types'
import {flattenChapters} from '#/plugin/lesson/resources/lesson/utils'
import {LessonCurrent} from '#/plugin/lesson/resources/lesson/components/current'

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
      onNavigate={props.onNavigate}
    >
      <section className="current-chapter">
        {props.chapter.poster &&
          <img className="chapter-poster img-responsive" alt={props.chapter.title} src={asset(props.chapter.poster)} />
        }

        <ContentTitle
          className="chapter-title"
          level={1}
          displayLevel={2}
          title={props.chapter.title}
          backAction={props.backAction}
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

Chapter.propTypes = {
  path: T.string.isRequired,
  chapter: T.shape(
    ChapterTypes.propTypes
  ),
  treeData: T.object,
  internalNotes: T.bool,
  backAction: T.object,
  onNavigate: T.func
}

Chapter.defaultProps = {
  internalNotes: false
}

export {
  Chapter
}