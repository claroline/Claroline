import React, {useCallback} from 'react'
import {PropTypes as T} from 'prop-types'
import {useDispatch, useSelector} from 'react-redux'
import {useHistory} from 'react-router-dom'
import classes from 'classnames'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {Html} from '#/main/app/components/html'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {PageContent, PageHeading, PageHeadingSkeleton, PageSection, PageToolbar} from '#/main/app/page'
import {Content, ContentSkeleton} from '#/main/app/components/content'
import {ContentPublication} from '#/main/app/content/components/publication'

import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {Chapter as ChapterTypes} from '#/plugin/lesson/resources/lesson/prop-types'
import {actions, selectors} from '#/plugin/lesson/resources/lesson/store'
import {MODAL_PAGE_HISTORY} from '#/plugin/lesson/resources/lesson/modals/history'
import {LessonPlayerNavSkeleton} from '#/plugin/lesson/resources/lesson/player/components/nav'

import {MODAL_PAGE_POSITION} from '#/plugin/lesson/resources/lesson/modals/position'
import {highlightSearch} from '#/plugin/lesson/resources/lesson/utils'

const Chapter = props => {
  const history = useHistory()
  const dispatch = useDispatch()

  const resourceNode = useSelector(resourceSelectors.resourceNode)
  const canEdit = hasPermission('edit', resourceNode)
  const downloadable = useSelector(resourceSelectors.downloadable)
  const embedded = useSelector(resourceSelectors.embedded)

  const lesson = useSelector(selectors.lesson)
  const root = useSelector(selectors.root)
  const pages = useSelector(selectors.pages)
  const showNavigation = useSelector(selectors.showNavigation)
  const showMeta = useSelector(selectors.showMeta)
  const currentSearch = useSelector(selectors.currentSearch)

  const downloadChapter = useCallback(() => {
    dispatch(actions.downloadChapterPdf(lesson.id, props.chapter.id))
  }, [lesson.id, props.chapter.slug])

  const deleteChapter = useCallback(() => {
    history.push(props.path+'/'+(props.chapter.previousSlug ? props.chapter.previousSlug : ''))
    dispatch(actions.deleteChapter(lesson.id, props.chapter.slug))
  }, [lesson.id, props.chapter.slug])

  const moveChapter = useCallback((position) => {
    dispatch(actions.moveChapter(lesson.id, props.chapter.slug, position))
  }, [lesson.id, props.chapter.slug])

  if (isEmpty(props.chapter)) {
    return (
      <PageContent  className={classes('placeholder-glow d-flex flex-column', {
        'mx-n4': embedded
      })}>
        {props.title &&
          <PageHeadingSkeleton />
        }

        <PageSection className="mb-5">
          <ContentSkeleton meta={showMeta} />
        </PageSection>

        {showNavigation &&
          <LessonPlayerNavSkeleton />
        }
      </PageContent>
    )
  }

  return (
    <>
      <PageToolbar
        toolbar="edit download more"
        actions={[
          {
            name: 'download',
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-download',
            label: trans('download', {}, 'actions'),
            displayed: downloadable,
            callback: downloadChapter,
            primary: true
          }, {
            name: 'edit',
            type: LINK_BUTTON,
            icon: 'fa fa-fw fa-pencil',
            label: trans('edit', {}, 'actions'),
            target: `${props.path}/${props.chapter.slug}/edit`,
            group: trans('management'),
            exact: true,
            displayed: canEdit
          }, {
            name: 'add-subpage',
            type: LINK_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add_subpage', {}, 'actions'),
            target: `${props.path}/new/${props.chapter.slug}`,
            group: trans('management'),
            displayed: canEdit
          }, {
            name: 'move',
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-arrows',
            label: trans('move', {}, 'actions'),
            modal: [MODAL_PAGE_POSITION, {
              icon: 'fa fa-fw fa-arrows',
              title: trans('movement'),
              root: root,
              page: props.chapter,
              pages: pages,
              saveLabel: trans('move', {}, 'actions'),
              onSave: moveChapter
            }],
            group: trans('management'),
            displayed: 1 !== pages.length && canEdit
          }, {
            name: 'show-history',
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-history',
            label: trans('show_history', {}, 'actions'),
            modal: [MODAL_PAGE_HISTORY, {
              pageId: props.chapter.id
            }],
            displayed: canEdit
          }, {
            name: 'delete',
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-trash',
            label: trans('delete', {}, 'actions'),
            callback: deleteChapter,
            dangerous: true,
            group: trans('management'),
            confirm: {
              message: trans('page_delete_message', {}, 'lesson'),
              additional: trans('irreversible_action_confirm')
            },
            displayed: canEdit
          }
        ]}
      />

      {props.title &&
        <PageHeading
          title={(
            <Html>
              {props.numbering ?
                props.numbering + ' ' + highlightSearch(props.chapter.title, currentSearch) :
                highlightSearch(props.chapter.title, currentSearch)
              }
            </Html>
          )}
          level={props.level}
          poster={props.poster}
        />
      }

      <PageSection className={classes('pb-5', !props.title && !embedded && 'mt-5')}>
        <Content
          placeholder={trans('no_content')}
          meta={showMeta ?
            <ContentPublication
              user={get(props.chapter, 'meta.creator', {})}
              publishedAt={get(props.chapter, 'meta.createdAt')}
            /> :
            undefined
          }
        >
          {highlightSearch(props.chapter.content, currentSearch)}
        </Content>
      </PageSection>

      {props.chapter.internalNote &&
        <PageSection
          className="pb-5"
        >
          <div className="bg-body-tertiary rounded-3 p-4" role="presentation">
            <h2 className="h5">{trans('internal_note')}</h2>
            <Html className="content-text">
              {highlightSearch(props.chapter.internalNote, currentSearch)}
            </Html>
          </div>
        </PageSection>
      }
    </>
  )
}

Chapter.propTypes = {
  path: T.string.isRequired,
  level: T.number,
  title: T.bool,
  poster: T.string,
  chapter: T.shape(
    ChapterTypes.propTypes
  ),
  numbering: T.string
}

export {
  Chapter
}
