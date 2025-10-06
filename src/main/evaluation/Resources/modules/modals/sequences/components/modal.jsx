import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {PickerModal} from '#/main/app/data/modals/picker/components/modal'
import {SequenceCard} from '#/main/evaluation/sequence/components/card'

const SequencesModal = (props) =>
  <PickerModal
    title={trans('sequences', {}, 'evaluation')}
    url={['apiv2_evaluation_sequence_context_list', {context: props.contextType, contextId: props.contextId}]}
    {...omit(props, 'contextType', 'contextId')}
    name="sequencesPicker"
    definition={[
      {
        name: 'name',
        type: 'string',
        label: trans('name'),
        displayed: true,
        primary: true
      }, {
        name: 'meta.description',
        type: 'string',
        label: trans('description'),
        sortable: false,
        displayed: true,
        options: {long: true}
      }, {
        name: 'code',
        type: 'string',
        label: trans('code')
      }, {
        name: 'tags',
        type: 'tag',
        label: trans('tags'),
        sortable: false,
        options: {
          objectClass: 'Claroline\\EvaluationBundle\\Entity\\Sequence'
        }
      }
    ]}
    card={SequenceCard}
  />

SequencesModal.propTypes = {
  selectAction: T.func.isRequired,
  multiple: T.bool,
  contextType: T.string,
  contextId: T.string,
  // from modal
  fadeModal: T.func.isRequired
}

export {
  SequencesModal
}
