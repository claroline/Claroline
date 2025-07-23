import React, {useState} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {url} from '#/main/app/api'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {trans, transChoice} from '#/main/app/intl/translation'
import {DataInput} from '#/main/app/data/components/input'
import {ConfirmModal} from '#/main/app/modals/confirm/components/modal'

import {Sequence} from '#/main/evaluation/sequence/prop-types'

const SequenceCopyModal = props => {
  let forceCopyResources = -1 !== props.sequences.findIndex((s) => s.workspace.id !== props.workspace.id)
  const [copyResources, setCopyResources] = useState(forceCopyResources)

  return (
    <ConfirmModal
      {...omit(props, 'sequences')}
      question={transChoice('copy_sequence_confirm_message', props.sequences.length, {count: '<b class="fw-bold">'+props.sequences.length+'</b>'}, 'evaluation')}
      items={props.sequences.map(item => ({
        thumbnail: item.poster,
        id: item.id,
        name: item.name
      }))}
      confirmAction={{
        type: ASYNC_BUTTON,
        label: trans('copy', {}, 'actions'),
        request: {
          url: url(['apiv2_evaluation_sequence_copy', {workspaceId: props.workspace.id}], {
            copyResources: copyResources
          }),
          request: {
            method: 'POST',
            body: JSON.stringify(props.sequences.map(sequence => sequence.id))
          },
          success: props.onCopy
        }
      }}
    >
      <DataInput
        className="mt-5"
        id="copySequenceResources"
        type="boolean"
        label={trans('sequence_copy_activities', {}, 'evaluation')}
        help={trans('sequence_copy_activities_help', {}, 'evaluation')}
        disabled={forceCopyResources}
        value={copyResources}
        onChange={(value) => setCopyResources(value)}
      />
    </ConfirmModal>
  )
}

SequenceCopyModal.propTypes = {
  workspace: T.shape({

  }).isRequired,
  sequences:  T.arrayOf(T.shape(
    Sequence.propTypes
  )),
  onCopy: T.func
}

export {
  SequenceCopyModal
}
