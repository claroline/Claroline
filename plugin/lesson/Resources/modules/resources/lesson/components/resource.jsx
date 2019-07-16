import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {url} from '#/main/app/api'

import {ResourcePage} from '#/main/core/resource/containers/page'
import {Routes} from '#/main/app/router'
import {LINK_BUTTON, DOWNLOAD_BUTTON} from '#/main/app/buttons'

import {ChapterResource} from '#/plugin/lesson/resources/lesson/components/chapter'
import {normalizeTree} from '#/plugin/lesson/resources/lesson/utils'
import {ChapterForm} from '#/plugin/lesson/resources/lesson/components/chapter-form'
import {Editor} from '#/plugin/lesson/resources/lesson/editor/components/editor'

class LessonResource extends Component {
  constructor(props) {
    super(props)

    this.reload()
  }

  componentDidUpdate(prevProps) {
    if (this.props.invalidated && this.props.invalidated !== prevProps.invalidated) {
      this.reload()
    }
  }

  reload() {
    if (this.props.invalidated) {
      this.props.fetchChapterTree(this.props.lesson.id)
    }
  }

  render() {
    return (
      <ResourcePage
        primaryAction="chapter"
        customActions={[
          {
            type: LINK_BUTTON,
            icon: 'fa fa-home',
            label: trans('show_overview'),
            target: this.props.path,
            exact: true
          },
          {
            type: DOWNLOAD_BUTTON,
            icon: 'fa fa-fw fa-file-pdf-o',
            displayed: this.props.canExport,
            label: trans('pdf_export'),
            file: {
              url: url(['icap_lesson_export_pdf', {lesson: this.props.lesson.id}])
            }
          }
        ]}
      >
        <Routes
          className="lesson-page-content"
          path={this.props.path}
          routes={[
            {
              path: '/',
              component: ChapterResource,
              exact: true
            }, {
              path: '/edit',
              component: Editor,
              exact: true
            }, {
              path: '/new',
              component: ChapterForm,
              exact: true,
              onEnter: () => this.props.createChapter(this.props.lesson.id, this.props.root.slug)
            }, {
              path: '/:slug',
              component: ChapterResource,
              exact: true,
              onEnter: params => this.props.loadChapter(this.props.lesson.id, params.slug)
            }, {
              path: '/:slug/edit',
              component: ChapterForm,
              exact: true,
              onEnter: params => this.props.editChapter(this.props.lesson.id, params.slug)
            }, {
              path: '/:slug/copy',
              component: ChapterForm,
              exact: true,
              onEnter: params => this.props.copyChapter(this.props.lesson.id, params.slug)
            }
          ]}
        />
      </ResourcePage>
    )
  }
}

LessonResource.propTypes = {
  path: T.string.isRequired,
  invalidated: T.bool.isRequired,
  fetchChapterTree: T.func.isRequired,
  lesson: T.any.isRequired,
  canExport: T.bool.isRequired,
  canEdit: T.bool.isRequired,
  tree: T.any.isRequired,
  root: T.any,
  createChapter: T.func.isRequired,
  copyChapter: T.func.isRequired,
  loadChapter: T.func.isRequired,
  editChapter: T.func.isRequired
}

export {
  LessonResource
}
