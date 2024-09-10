import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useHistory} from 'react-router-dom'

import {displayDate, trans} from '#/main/app/intl'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ContentHtml} from '#/main/app/content/components/html'
import {PageSection} from '#/main/app/page'
import {ResourcePage} from '#/main/core/resource'

import {Announcement as AnnouncementTypes} from '#/plugin/announcement/resources/announcement/prop-types'
import {MODAL_ANNOUNCEMENT_SENDING} from '#/plugin/announcement/resources/announcement/modals/sending'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'
import {UserMicro} from '#/main/core/user/components/micro'


const AnnouncementPost = (props) => {
  const history = useHistory()

  return (
    <ResourcePage
      title={props.announcement.title}
      poster={props.announcement.poster}
      primaryAction="edit"
      actions={[
        {
          name: 'download',
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-file-pdf',
          label: trans('export-pdf',{}, 'actions'),
          callback: props.exportPDF
        }, {
          name: 'send',
          type: MODAL_BUTTON,
          icon: 'fa fa-fw fa-paper-plane',
          label: trans('send', {}, 'actions'),
          target: `${props.path}/${props.announcement.id}/send`,
          modal: [MODAL_ANNOUNCEMENT_SENDING, {
            aggregateId: props.aggregateId,
            announcement: props.announcement,
            workspaceRoles: props.workspaceRoles
          }],
          displayed: props.editable
        }, {
          name: 'edit',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-pencil',
          label: trans('edit', {}, 'actions'),
          target: `${props.path}/${props.announcement.id}/edit`,
          displayed: props.editable
        }, {
          name: 'delete',
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-trash',
          label: trans('delete', {}, 'actions'),
          callback: () => {
            props.remove()
            history.push(props.path)
          },
          dangerous: true,
          confirm: {
            title: trans('announcement_delete_confirm_title', {}, 'announcement'),
            message: trans('announcement_delete_confirm_message', {}, 'announcement'),
          },
          displayed: props.editable
        }
      ]}
    >
      <PageSection size="md" className="py-5 px-4">
        <div className="text-body-tertiary fw-bolder d-flex align-items-center gap-3 mb-4" role="presentation">
          <UserMicro
            {...get(props.announcement, 'meta.creator', {})}
            noStatus={true}
            link={true}
          />

          <span>-</span>

          {get(props.announcement, 'meta.publishedAt') &&
            <span className="">{displayDate(get(props.announcement, 'meta.publishedAt') , true)}</span>
          }
        </div>

        <ContentHtml className="lead">{props.announcement.content}</ContentHtml>

        {!isEmpty(props.announcement.tags) &&
          <div className="mt-4" role="presentation">
            {props.announcement.tags.map(tag =>
              <span key={tag} className="badge text-secondary-emphasis bg-secondary-subtle fs-sm lh-base">{tag}</span>
            )}
          </div>
        }
      </PageSection>
    </ResourcePage>
  )
}

AnnouncementPost.propTypes = {
  path: T.string.isRequired,
  aggregateId: T.string.isRequired,
  editable: T.bool,
  announcement: T.shape(
    AnnouncementTypes.propTypes
  ).isRequired,
  workspaceRoles: T.array,
  exportPDF: T.func.isRequired,
  remove: T.func.isRequired
}

export {
  AnnouncementPost
}
