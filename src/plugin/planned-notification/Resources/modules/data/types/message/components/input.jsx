import React from 'react'
import {connect} from 'react-redux'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {actions as modalActions} from '#/main/app/overlays/modal/store'
import {MODAL_DATA_LIST} from '#/main/app/modals/list'
import {trans} from '#/main/app/intl/translation'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {ContentHtml} from '#/main/app/content/components/html'

import {selectors} from '#/plugin/planned-notification/tools/planned-notification/store'
import {Message as MessageType} from '#/plugin/planned-notification/tools/planned-notification/prop-types'

// todo : maybe move it in core

const MessageInputComponent = props =>
  <div
    className="message-form-group"
  >
    {props.disabled ?
      <input
        type="text"
        className="form-control"
        value={props.value ? props.value.title : ''}
        readOnly={true}
      /> :
      <div className="input-group">
        <input
          type="text"
          className="form-control"
          value={props.value ? props.value.title : ''}
          readOnly={true}
        />
        <span className="input-group-btn">
          <button
            type="button"
            className="btn btn-default"
            onClick={() => props.pickMessage(props.workspace.id, props)}
          >
            <span className="fa fa-fw fa-envelope" />
          </button>
        </span>
      </div>
    }
  </div>

implementPropTypes(MessageInputComponent, DataInputTypes, {
  value: T.shape(MessageType.propTypes),
  workspace: T.shape({
    uuid: T.string.isRequired
  }).isRequired,
  disabled: T.bool.isRequired,
  pickMessage: T.func.isRequired
}, {})

const MessageInput = connect(
  state => ({
    workspace: selectors.workspace(state)
  }),
  dispatch => ({
    pickMessage(worskpaceUuid, props) {
      dispatch(modalActions.showModal(MODAL_DATA_LIST, {
        icon: 'fa fa-fw fa-envelope',
        title: trans('select_message', {}, 'planned_notification'),
        confirmText: trans('select', {}, 'planned_notification'),
        name: selectors.STORE_NAME+'.messages.picker',
        onlyId: false,
        definition: [
          {
            name: 'title',
            type: 'string',
            label: trans('title'),
            displayed: true,
            primary: true
          },
          {
            name: 'content',
            type: 'string',
            label: trans('content'),
            displayed: true,
            primary: false,
            render: (row) => {
              let contentRow =
                <ContentHtml>
                  {row.content}
                </ContentHtml>

              return contentRow
            }
          }
        ],
        fetch: {
          url: ['apiv2_plannednotificationmessage_workspace_list', {workspace: worskpaceUuid}],
          autoload: true
        },
        handleSelect: (selected) => props.onChange(selected[0])
      }))
    }
  })
)(MessageInputComponent)

export {
  MessageInput
}