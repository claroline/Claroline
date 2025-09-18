import React, {useMemo} from 'react'
import {useDispatch, useSelector} from 'react-redux'
import isEmpty from 'lodash/isEmpty'

import {hasPermission, selectors as securitySelectors} from '#/main/app/security'
import {DashboardActions} from '#/main/app/dashboard/components/actions'

import {getActions} from '#/main/evaluation/sequence/utils'
import {selectors, actions} from '#/main/evaluation/sequence/store'

const SequenceDashboardActions = () => {
  const dispatch = useDispatch()

  const currentUser = useSelector(securitySelectors.currentUser)
  const sequencePath = useSelector(selectors.path)
  const sequence = useSelector(selectors.sequence)

  const sequenceActions = useMemo(() => {
    if (!isEmpty(sequence)) {
      return getActions([sequence], {
        update: (updatedSequences) => {
          dispatch(actions.reload(updatedSequences[0]))
        }
      }, sequencePath, currentUser)
    }

    return []
  }, [sequence])

  return (
    <DashboardActions
      canAdministrate={hasPermission('administrate', sequence)}
      actions={sequenceActions}
    />
  )
}

export {
  SequenceDashboardActions
}
