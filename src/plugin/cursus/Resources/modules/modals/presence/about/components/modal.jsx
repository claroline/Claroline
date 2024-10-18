import React from 'react'
import omit from 'lodash/omit'

import {displayDate} from '#/main/app/intl'
import {trans} from '#/main/app/intl/translation'

import {ContentHtml} from '#/main/app/content/components/html'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {DetailsData} from '#/main/app/content/details/components/data'
import {FileThumbnail} from '#/main/app/data/types/file/components/thumbnail'

const EvidenceAboutModal = (props) =>
  <Modal
    {...omit(props)}
    icon="fa fa-fw fa-file-invoice"
    title={trans('Justificatif de présence')}
  >
    <div className="modal-body">
      <div className="bg-body-secondary rounded-2 p-4">
        <ContentHtml className="mb-3">
          {trans('evidence_info', {
            user: '<span class="text-uppercase">' + props.presence.user.name + '</span>',
            event_title: '<span class="text-uppercase">' + props.presence.event.name + '</span>',
            event_datetime_start: '<span class="fw-bold text-uppercase">' + displayDate(props.presence.event.start, true, true) + '</span>',
            event_datetime_end: '<span class="fw-bold text-uppercase">' + displayDate(props.presence.event.end, true, true) + '</span>'
          }, 'presence')}
        </ContentHtml>
      </div>

      <DetailsData
        flush={true}
        data={props.presence}
        definition={[
          {
            title: trans('general'),
            primary: true,
            fields: [
              {
                name: 'evidence_added_by',
                type: 'user',
                label: trans('evidence_added_by', {}, 'presence')
              }, {
                name: 'evidence_added_at',
                type: 'date',
                label: trans('evidence_added_at', {}, 'presence'),
                displayed: true,
                options: {
                  time: true
                }
              }, {
                name: 'file',
                type: 'file',
                label: trans('file'),
                render: () => (
                  <FileThumbnail
                    file={props.presence.evidences[0]}
                    download={{url: ['apiv2_cursus_presence_evidence_download', {id: props.presence.id, file: props.presence.evidences[0]}]}}
                  />
                )
              }
            ]
          }
        ]}
      />
    </div>
  </Modal>

export {
  EvidenceAboutModal
}
