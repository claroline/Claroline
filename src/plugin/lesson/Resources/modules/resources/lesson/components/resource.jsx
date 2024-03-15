import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {scrollTo} from '#/main/app/dom/scroll'
import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {ResourcePage} from '#/main/core/resource/containers/page'

import {Chapter} from '#/plugin/lesson/resources/lesson/containers/chapter'
import {ChapterForm} from '#/plugin/lesson/resources/lesson/components/chapter-form'
import {Editor} from '#/plugin/lesson/resources/lesson/editor/containers/editor'
import {LessonOverview} from '#/plugin/lesson/resources/lesson/containers/overview'
import {ChapterList} from '#/plugin/lesson/resources/lesson/containers/list'
import {getNumbering, flattenChapters} from '#/plugin/lesson/resources/lesson/utils'
import {constants as LESSON_NUMBERINGS} from '#/plugin/lesson/resources/lesson/constants'
import {LessonSummary} from '#/plugin/lesson/resources/lesson/containers/summary'

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

  newChapterNumbering(props, slug = null) {
    if(slug === null) {
      const numbering = props.tree.children ? props.tree.children.length + 1 : 1
      if (props.lesson.display.numbering === LESSON_NUMBERINGS.NUMBERING_LITERAL) {
        return 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'[numbering - 1]
      }
      return numbering
    }

    const chapters = flattenChapters(props.tree.children, true)
    const parentChapter = chapters.find(chapter => chapter.slug === slug)
    const parentNumbering = parentChapter ? getNumbering(props.lesson.display.numbering, props.tree.children, parentChapter) : 1
    const numbering = parentChapter && parentChapter.children ? parentChapter.children.length + 1 : 1

    if (props.lesson.display.numbering === LESSON_NUMBERINGS.NUMBERING_LITERAL) {
      return parentNumbering + '.' + 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'[numbering - 1]
    }

    return parentNumbering + '.' + numbering
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
          /*{
            type: LINK_BUTTON,
            icon: 'fa fa-fw fa-home',
            label: trans('show_overview'),
            displayed: this.props.overview,
            target: this.props.path,
            exact: true
          }, */{
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-file-pdf',
            displayed: this.props.canExport,
            label: trans('export-pdf', {}, 'actions'),
            group: trans('transfer'),
            callback: () => this.props.downloadLessonPdf(this.props.lesson.id)
          }
        ]}
        redirect={[
          {from: '/', exact: true, to: '/'+get(this.props.tree, 'children[0].slug'), disabled: this.props.overview || !get(this.props.tree, 'children[0]')}
        ]}
        routes={[
          {
            path: '/summary',
            component: LessonSummary
          }, {
            path: '/chapters',
            component: ChapterList
          }, {
            path: '/edit',
            component: Editor
          }, {
            path: '/new',
            render: () => (
              <ChapterForm
                numbering={this.newChapterNumbering(this.props)}
              />
            ),
            onEnter: () => this.props.createChapter(this.props.lesson.id, this.props.root.slug)
          }, {
            path: '/:slug',
            exact: true,
            onEnter: params => this.props.loadChapter(this.props.lesson.id, params.slug),
            render: () => (
              <Chapter
                backAction={this.props.overview ? {
                  type: LINK_BUTTON,
                  target: this.props.path,
                  exact: true
                } : undefined}
                onNavigate={() => scrollTo(`#resource-${this.props.resourceId} > .page-content`)}
              />
            )
          }, {
            path: '/:slug/subchapter',
            render: (props) => (
              <ChapterForm
                numbering={this.newChapterNumbering(this.props, props.match.params.slug)}
              />
            ),
            onEnter: params => this.props.createChapter(this.props.lesson.id, params.slug)
          }, {
            path: '/:slug/edit',
            component: ChapterForm,
            onEnter: params => this.props.editChapter(this.props.lesson.id, params.slug)
          }, {
            path: '/:slug/copy',
            component: ChapterForm,
            onEnter: params => this.props.copyChapter(this.props.lesson.id, params.slug)
          }, {
            path: '/',
            exact: true,
            component: LessonOverview,
            disabled: !this.props.overview
          }
        ]}
      />
    )
  }
}

LessonResource.propTypes = {
  path: T.string.isRequired,
  resourceId: T.string,
  invalidated: T.bool.isRequired,
  fetchChapterTree: T.func.isRequired,
  lesson: T.any.isRequired,
  overview: T.bool.isRequired,
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
