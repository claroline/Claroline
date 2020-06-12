import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Alert} from '#/main/app/alert/components/alert'
import {ResourcePage} from '#/main/core/resource/containers/page'

import {ChapterResource} from '#/plugin/lesson/resources/lesson/components/chapter'
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
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-file-pdf-o',
            displayed: this.props.canExport,
            label: trans('export-pdf', {}, 'actions'),
            group: trans('transfer'),
            callback: () => this.props.downloadLessonPdf(this.props.lesson.id)
          }
        ]}
        redirect={[
          {from: '/', exact: true, to: '/'+get(this.props.tree, 'children[0].slug'), disabled: !get(this.props.tree, 'children[0]')}
        ]}
        routes={[
          {
            path: '/edit',
            component: Editor
          }, {
            path: '/new',
            component: ChapterForm,
            onEnter: () => this.props.createChapter(this.props.lesson.id, this.props.root.slug)
          }, {
            path: '/:slug',
            component: ChapterResource,
            exact: true,
            onEnter: params => this.props.loadChapter(this.props.lesson.id, params.slug)
          }, {
            path: '/:slug/edit',
            component: ChapterForm,
            onEnter: params => this.props.editChapter(this.props.lesson.id, params.slug)
          }, {
            path: '/:slug/copy',
            component: ChapterForm,
            onEnter: params => this.props.copyChapter(this.props.lesson.id, params.slug)
          }
        ]}
      >
        {0 === get(this.props.tree, 'children', []).length &&
          <Alert type="info">
            {trans('empty_lesson_message', {}, 'icap_lesson')}
          </Alert>
        }
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
  editChapter: T.func.isRequired,
  downloadLessonPdf: T.func.isRequired
}

export {
  LessonResource
}
