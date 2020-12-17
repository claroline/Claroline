import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {Vertical} from '#/main/app/content/tabs/components/vertical'

import {ImportForm} from '#/main/core/tools/transfer/import/containers/form'

const ImportTab = props => {
  const entities = Object.keys(props.explanation)
    .map((key) => ({
      title: trans(key, {}, 'transfer'),
      path: `/${key}`
    }))
    .sort((a, b) => (a.title < b.title) ? -1 : 1)

  return (
    <div className="row">
      <div className="col-md-3">
        <Vertical
          style={{
            marginTop: 20 // FIXME
          }}
          basePath={`${props.path}/import`}
          tabs={entities}
        />
      </div>

      <div className="col-md-9">
        <Routes
          path={props.path + '/import'}
          redirect={!isEmpty(entities) ? [
            {from: '/', exact: true, to: entities[0].path}
          ] : undefined}
          routes={[{
            path: '/:entity/:action?',
            component: ImportForm,
            onEnter: (params) => props.openForm(params)
          }]}
        />
      </div>
    </div>
  )
}

ImportTab.propTypes = {
  path: T.string.isRequired,
  explanation: T.object,
  openForm: T.func.isRequired
}

export {
  ImportTab
}
