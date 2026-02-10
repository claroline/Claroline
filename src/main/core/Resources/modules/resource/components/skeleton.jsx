import React from 'react'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl'
import {PageContext} from '#/main/app/page/context'
import {ContextPage} from '#/main/app/context'
import {selectors as toolSelectors} from '#/main/core/tool'

import {selectors} from '#/main/core/resource/store'

const ResourceSkeleton = () => {
  const toolPath = useSelector(toolSelectors.path)
  const embedded = useSelector(selectors.embedded)

  if (embedded) {
    return (
      <div className="bg-body-tertiary ratio ratio-16x9 rounded-3">
        <div className="d-flex flex-column justify-content-center align-items-center p-4 text-body-secondary">
          <div className="dot-elastic" aria-hidden={true} />
          <p className="mt-3 mb-0 fs-sm fw-bolder">{trans('loading')}</p>
        </div>
      </div>
    )
  }

  return (
    <PageContext.Provider
      value={{
        embedded: embedded
      }}
    >
      <ContextPage
        breadcrumb={[
          {
            label: trans('loading'),
            target: toolPath
          }
        ]}
      >
        <div className="d-flex flex-column justify-content-center align-items-center p-4 m-auto text-body-secondary">
          <div className="dot-elastic" aria-hidden={true} />
          <p className="mt-3 mb-0 fs-sm fw-bolder">{trans('loading')}</p>
        </div>
      </ContextPage>
    </PageContext.Provider>
  )
}

export {
  ResourceSkeleton
}
