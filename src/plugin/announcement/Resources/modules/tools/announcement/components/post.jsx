import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useDispatch} from 'react-redux'
import {useHistory} from 'react-router-dom'
import get from 'lodash/get'

import {trans, transChoice} from '#/main/app/intl/translation'
import {Badge} from '#/main/app/components/badge'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON, ModalButton} from '#/main/app/buttons'
import {PageContent, PageHeading, PageHeadingSkeleton, PageSection} from '#/main/app/page'
import {Content, ContentSkeleton} from '#/main/app/components/content'
import {ContentPublication} from '#/main/app/content/components/publication'
import {ToolPage} from '#/main/core/tool'

import {Announcement as AnnouncementTypes} from '#/plugin/announcement/prop-types'
import {MODAL_ANNOUNCEMENT_SENDING} from '#/plugin/announcement/tools/announcement/modals/sending'
import {PageToolbar, PageToolbarSkeleton} from '#/main/app/page/components/toolbar'
import {MODAL_ANNOUNCEMENT_FORM} from '#/plugin/announcement/announcement/modals/form'
import {MODAL_VIEWERS} from '#/main/app/modals/viewers'
import {actions} from '#/plugin/announcement/tools/announcement/store'
import {hasPermission} from '#/main/app/security'

const AnnouncementPost = (props) => {
  const dispatch = useDispatch()
  const history = useHistory()

  return (
    <ToolPage
      title={trans('announcement_name', {name: get(props.announcement, 'title', trans('loading'))}, 'announcement')}
    >
      {!props.announcement &&
        <PageContent className="placeholder-glow">
          <PageToolbarSkeleton toolbar="edit more" />
          <PageHeadingSkeleton
            backAction={trans('back_to_announcements', {}, 'announcement')}
          />
          <PageSection className="mb-5">
            <ContentSkeleton meta={true} />
          </PageSection>
        </PageContent>
      }

      {props.announcement &&
        <PageContent poster={props.announcement.poster}>
          <PageToolbar
            actions={[
              {
                name: 'edit',
                type: MODAL_BUTTON,
                icon: 'fa fa-fw fa-pencil',
                label: trans('edit', {}, 'actions'),
                target: `${props.path}/${props.announcement.id}/edit`,
                displayed: props.editable,
                modal: [MODAL_ANNOUNCEMENT_FORM, {
                  announcement: props.announcement,
                  onSave: (announcement) => {
                    dispatch(actions.changeAnnounce(announcement))
                  }
                }]
              }, {
                name: 'download',
                type: CALLBACK_BUTTON,
                icon: 'fa fa-fw fa-download',
                label: trans('export-pdf',{}, 'actions'),
                callback: () => props.exportPDF(props.announcement)
              }, {
                name: 'send',
                type: MODAL_BUTTON,
                icon: 'fa fa-fw fa-paper-plane',
                label: trans('send', {}, 'actions'),
                target: `${props.path}/${props.announcement.id}/send`,
                modal: [MODAL_ANNOUNCEMENT_SENDING, {
                  announcement: props.announcement,
                  onSend: (announcement) => {
                    dispatch(actions.changeAnnounce(announcement))
                  }
                }],
                displayed: props.editable
              }, {
                name: 'delete',
                type: CALLBACK_BUTTON,
                icon: 'fa fa-fw fa-trash',
                label: trans('delete', {}, 'actions'),
                callback: () => {
                  props.remove(props.announcement)
                  history.push(props.path)
                },
                dangerous: true,
                confirm: trans('announcement_delete_confirm_message', {}, 'announcement'),
                displayed: props.editable
              }
            ]}
          />

          <PageHeading
            backAction={{
              type: LINK_BUTTON,
              label: trans('back_to_announcements', {}, 'announcement'),
              target: props.path,
              exact: true
            }}
            title={props.announcement.title}
          />

          <PageSection className="mb-5">
            <Content
              placeholder={trans('no_content')}
              meta={
                <>
                  <ContentPublication
                    user={get(props.announcement, 'meta.creator', {})}
                    publishedAt={get(props.announcement, 'meta.publishedAt')}
                  />
                  {hasPermission('follow', props.announcement) && (
                    <ModalButton
                      className="ms-auto btn btn-link p-0 border-0"
                      modal={[MODAL_VIEWERS, {
                        url: ['claro_announcement_views', {id: props.announcement.id}]
                      }]}
                    >
                      <Badge variant="secondary" subtle={true} className="lh-base">
                        <span className="fa fa-eye me-2" aria-hidden={true} />
                        {transChoice('display_views', get(props.announcement, 'meta.views', 0), {count: get(props.announcement, 'meta.views', 0)})}
                      </Badge>
                    </ModalButton>
                  )}
                </>
              }
              tags={props.announcement.tags}
            >
              {props.announcement.content}
            </Content>
          </PageSection>
        </PageContent>
      }
    </ToolPage>
  )
}

AnnouncementPost.propTypes = {
  path: T.string.isRequired,
  editable: T.bool,
  announcement: T.shape(
    AnnouncementTypes.propTypes
  ).isRequired,
  exportPDF: T.func.isRequired,
  remove: T.func.isRequired
}

export {
  AnnouncementPost
}
