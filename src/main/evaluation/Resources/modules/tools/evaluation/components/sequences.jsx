import React from 'react'
import {useDispatch, useSelector} from 'react-redux'

import {trans} from '#/main/app/intl'
import {MODAL_BUTTON} from '#/main/app/buttons'
import {PageListSection} from '#/main/app/page'
import {ToolPage, selectors as toolSelectors} from '#/main/core/tool'

import {SequenceList} from '#/main/evaluation/sequence/components/list'
import {selectors} from '#/main/evaluation/tools/evaluation/store'
import {MODAL_SEQUENCE_CREATION} from '#/main/evaluation/sequence/modals/creation'
import {actions as listActions} from '#/main/app/content/list'

const EvaluationSequences = () => {
  const dispatch = useDispatch()
  
  const toolPath = useSelector(toolSelectors.path)
  const contextId = useSelector(toolSelectors.contextId)
  const contextName = useSelector(toolSelectors.contextType)
  const contextData = useSelector(toolSelectors.contextData)

  return (
    <ToolPage title={trans('sequences', {}, 'evaluation')}>
      <PageListSection
        title={trans('sequences', {}, 'evaluation')}
        addAction={{
          icon: 'fa fa-fw fa-plus',
          label: trans('add_sequence', {}, 'actions'),
          type: MODAL_BUTTON,
          displayed: 'workspace' === contextName,
          modal: [MODAL_SEQUENCE_CREATION, {
            workspace: contextData,
            onCreate: () => dispatch(listActions.invalidateData(selectors.STORE_NAME+'.sequences'))
          }]
        }}
      >
        <SequenceList
          className="mb-5"
          path={toolPath}
          name={selectors.STORE_NAME+'.sequences'}
          flush={true}
          url={['apiv2_evaluation_sequence_context_list', {context: contextName, contextId: contextId}]}
        />
      </PageListSection>
    </ToolPage>
  )
}

export {
  EvaluationSequences
}
