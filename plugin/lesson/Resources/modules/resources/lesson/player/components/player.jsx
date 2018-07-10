import React, {Component} from 'react'
import {connect} from 'react-redux'
import {trans} from '#/main/core/translation'
import {url} from '#/main/app/api'
import {PropTypes as T} from '#/main/core/scaffolding/prop-types'
import {selectors as resourceSelect} from '#/main/core/resource/store'
import {hasPermission} from '#/main/core/resource/permissions'

import {ResourcePageContainer} from '#/main/core/resource/containers/page'
import {RoutedPageContent} from '#/main/core/layout/router'
import {PageContent} from '#/main/core/layout/page/index'
import {SummarizedContent} from '#/main/app/content/summary/components/content'

import {actions} from '#/plugin/lesson/resources/lesson/store/'
import {constants} from '#/plugin/lesson/resources/lesson/constants'
import {ChapterResource} from '#/plugin/lesson/resources/lesson/components/chapter'
import {normalizeTree} from '#/plugin/lesson/resources/lesson/components/tree/utils'
import {ChapterForm} from '#/plugin/lesson/resources/lesson/components/chapter-form'

class PlayerComponent extends Component {
  constructor(props) {
    super(props)

    this.reload()
  }

  componentDidUpdate(prevProps) {
    if (this.props.invalidated && this.props.invalidated !== prevProps.tree.invalidated) {
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
      <ResourcePageContainer
        customActions={[
          {
            type: 'link',
            icon: 'fa fa-home',
            label: trans('show_overview'),
            target: '/',
            exact: true
          },
          {
            type: 'download',
            icon: 'fa fa-fw fa-file-pdf-o',
            displayed: this.props.canExport,
            label: trans('pdf_export'),
            file: {
              url: url(['icap_lesson_export_pdf', {lesson: this.props.lesson.id}])
            }
          }
        ]}
      >
        <PageContent>
          <SummarizedContent
            summary={{
              displayed: true,
              opened: true,
              pinned: true,
              title: trans('summary'),
              links: normalizeTree(this.props.tree, this.props.lesson.id, this.props.canEdit).children
            }}
          >
            <RoutedPageContent className="lesson-page-content" routes={[
              {
                path: '/',
                component: ChapterResource,
                exact: true
              },
              {
                path: '/new',
                component: ChapterForm,
                exact: true,
                onEnter: () => this.props.createChapter(this.props.lesson.id)
              },
              {
                path: '/:slug',
                component: ChapterResource,
                exact: true,
                onEnter: params => this.props.loadChapter(this.props.lesson.id, params.slug)
              },
              {
                path: '/:slug/edit',
                component: ChapterForm,
                exact: true,
                onEnter: params => this.props.editChapter(this.props.lesson.id, params.slug)
              },
              {
                path: '/:slug/copy',
                component: ChapterForm,
                exact: true,
                onEnter: params => this.props.copyChapter(this.props.lesson.id, params.slug)
              }
            ]}/>
          </SummarizedContent>
        </PageContent>
      </ResourcePageContainer>
    )
  }
}


PlayerComponent.propTypes = {
  invalidated: T.bool.isRequired,
  fetchChapterTree: T.func.isRequired,
  lesson: T.any.isRequired,
  canExport: T.bool.isRequired,
  canEdit: T.bool.isRequired,
  tree: T.any.isRequired,
  createChapter: T.func.isRequired,
  copyChapter: T.func.isRequired,
  loadChapter: T.func.isRequired,
  editChapter: T.func.isRequired
}

const Player = connect(
  state => ({
    lesson: state.lesson,
    tree: state.tree.data,
    invalidated: state.tree.invalidated,
    canExport: hasPermission('export', resourceSelect.resourceNode(state)) && state.exportPdfEnabled,
    canEdit: hasPermission('edit', resourceSelect.resourceNode(state))
  }),
  dispatch => ({
    loadChapter: (lessonId, chapterSlug) => {
      dispatch(actions.loadChapter(lessonId, chapterSlug))
    },
    editChapter: (lessonId, chapterSlug) => {
      dispatch(actions.editChapter(constants.CHAPTER_EDIT_FORM_NAME, lessonId, chapterSlug))
    },
    copyChapter: (lessonId, chapterSlug) => {
      dispatch(actions.copyChapter(constants.CHAPTER_EDIT_FORM_NAME, lessonId, chapterSlug))
    },
    createChapter: (lessonId, parentChapterSlug = null) => {
      dispatch(actions.createChapter(constants.CHAPTER_EDIT_FORM_NAME, lessonId, parentChapterSlug))
    },
    fetchChapterTree: lessonId => dispatch(actions.fetchChapterTree(lessonId))
  })
)(PlayerComponent)

export {
  Player
}