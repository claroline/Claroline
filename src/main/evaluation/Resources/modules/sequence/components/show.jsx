import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector} from 'react-redux'

import {Routes} from '#/main/app/router'
import {useFetch} from '#/main/app/api/fetch'
import {hasPermission} from '#/main/app/security'

import {selectors} from '#/main/evaluation/sequence/store'
import {SequenceOverview} from '#/main/evaluation/sequence/components/overview'
import {SequenceEditor} from '#/main/evaluation/sequence/editor'
import {SequencePlayer} from '#/main/evaluation/sequence/player'
import {SequenceDashboard} from '#/main/evaluation/sequence/dashboard'
import {PageContent, PageHeadingSkeleton, PageSection} from '#/main/app/page'
import {SequencePage} from '#/main/evaluation/sequence/components/page'
import {SequenceError} from '#/main/evaluation/sequence/components/error'
import {TextSkeleton} from '#/main/app/components/placeholder'

const SequenceShow = props => {
  const [sequence, status, error, errorCode] = useFetch(selectors.STORE_NAME, ['apiv2_evaluation_sequence_open', {id: props.id}])

  const firstStepNotDone = useSelector(selectors.firstStepNotDone)

  console.log(error)

  if ('succeeded' === status) {
    return (
      <Routes
        path={props.path+'/'+props.id}
        redirect={[
          {from: '/continue', to: '/play/'+(firstStepNotDone ? firstStepNotDone.slug : null), disabled: !firstStepNotDone}
        ]}
        routes={[
          {
            path: '',
            exact: true,
            component: SequenceOverview
          }, {
            path: '/play',
            component: SequencePlayer
          }, {
            path: '/edit',
            component: SequenceEditor,
            disabled: !hasPermission('edit', sequence.sequence)
          }, {
            path: '/dashboard',
            disabled: !hasPermission('follow', sequence.sequence),
            component: SequenceDashboard
          }
        ]}
      />
    )
  }

  if ('failed' === status) {
    let sequenceError
    switch (errorCode) {
      case 404:
        sequenceError =  {code: 'NOT_FOUND', message: 'Sequence not found.'}
        break
      case 500:
        sequenceError =  {code: 'UNKNOWN_ERROR', message: error.message, additional: error.trace}
        break
      case 401:
      case 403:
        sequenceError = error
        break
    }

    return (
      <SequenceError {...sequenceError} />
    )
  }

  return (
    <SequencePage>
      <PageContent className="placeholder-glow">
        <PageHeadingSkeleton
          description={true}
        />
        <PageSection className="mb-5">
          <TextSkeleton />
        </PageSection>
      </PageContent>
    </SequencePage>
  )
}

SequenceShow.propTypes = {
  path: T.string.isRequired,
  id: T.string.isRequired
}

export {
  SequenceShow
}
