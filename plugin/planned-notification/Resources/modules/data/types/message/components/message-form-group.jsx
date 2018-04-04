import React from 'react'
import {connect} from 'react-redux'

import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {MODAL_DATA_PICKER} from '#/main/core/data/list/modals'
import {trans} from '#/main/core/translation'
import {FormGroup as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group.jsx'
import {HtmlText} from '#/main/core/layout/components/html-text.jsx'

import {select} from '#/plugin/planned-notification/tools/planned-notification/selectors'
import {Message as MessageTypes} from '#/plugin/planned-notification/tools/planned-notification/prop-types'

const MessageFormGroupComponent = props =>
  <FormGroup
    {...props}
    error={props.error && typeof props.error === 'string' ? props.error : undefined}
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
            onClick={() => props.pickMessage(props.workspace.uuid, props)}
          >
            <span className="fa fa-fw fa-envelope"></span>
          </button>
        </span>
      </div>
    }
  </FormGroup>

implementPropTypes(MessageFormGroupComponent, FormGroupWithFieldTypes, {
  value: T.shape(MessageTypes.propTypes),
  workspace: T.shape({
    uuid: T.string.isRequired
  }).isRequired,
  disabled: T.bool.isRequired,
  pickMessage: T.func.isRequired
}, {})

const MessageFormGroup = connect(
  state => ({
    workspace: select.workspace(state)
  }),
  dispatch => ({
    pickMessage(worskpaceUuid, props) {
      dispatch(modalActions.showModal(MODAL_DATA_PICKER, {
        icon: 'fa fa-fw fa-envelope',
        title: trans('select_message', {}, 'planned_notification'),
        confirmText: trans('select', {}, 'planned_notification'),
        name: 'messages.picker',
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
            renderer: (row) => {
              let contentRow =
                <HtmlText>
                  {row.content}
                </HtmlText>

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
)(MessageFormGroupComponent)

export {
  MessageFormGroup
}