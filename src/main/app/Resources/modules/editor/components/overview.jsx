import React from 'react'
import {PropTypes as T} from 'prop-types'

import {displayDate, trans} from '#/main/app/intl'
import {EditorPage} from '#/main/app/editor'
import {ClipboardButton} from '#/main/app/buttons'
import {Badge} from '#/main/app/components/badge'
import {TooltipOverlay} from '#/main/app/overlays/tooltip/components/overlay'

const EditorOverview = (props) =>
  <EditorPage
    {...props}
    title={trans('overview')}
    meta={props.meta && (
      <div className="mt-2 d-flex flex-row gap-3">
        {props.meta.id &&
          <TooltipOverlay
            tip={trans('clipboard_copy', {}, 'actions')}
            position="bottom"
          >
            <ClipboardButton copy={() => props.meta.id} className="btn btn-link lh-sm p-0 border-0 rounded-1">
              <Badge subtle={true}>
                <b>ID:</b> {props.meta.id}
              </Badge>
            </ClipboardButton>
          </TooltipOverlay>
        }

        {props.meta.updatedAt &&
          <p className="mb-0 ms-auto fs-sm text-body-secondary">
            {trans('last_modified_at', {date: displayDate(props.meta.updatedAt, false, true)})}
          </p>
        }
      </div>
    )}
  />

EditorOverview.propTypes = {
  meta: T.shape({
    id: T.string,
    updatedAt: T.string
  })
}

export {
  EditorOverview
}
