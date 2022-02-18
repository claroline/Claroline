import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'
import merge from 'lodash/merge'

import {trans} from '#/main/app/intl/translation'
import {Routes, withRouter} from '#/main/app/router'
import {Vertical} from '#/main/app/content/tabs/components/vertical'
import {ToolPage} from '#/main/core/tool/containers/page'

const TransferForm = withRouter(props => {
  const entities = Object.keys(props.explanation)
    .map((key) => ({
      title: trans(key, {}, 'transfer'),
      path: `/${key}`
    }))
    .sort((a, b) => (a.title < b.title) ? -1 : 1)

  return (
    <ToolPage subtitle={props.title}>
      <div className="row">
        <div className="col-md-3">
          <Vertical
            style={{
              marginTop: 20
            }}
            basePath={props.path}
            tabs={entities}
          />
        </div>

        <div className="col-md-9">
          <Routes
            path={props.path}
            redirect={!isEmpty(entities) ? [
              {from: '/', exact: true, to: entities[0].path}
            ] : undefined}
            routes={[{
              path: '/:entity/:action?',
              onEnter: (params) => props.openForm(!isEmpty(props.contextData) ? merge({}, params, {workspace: props.contextData}) : params),
              render: (routerProps) => React.cloneElement(props.children, merge({}, props.children.props, routerProps))
            }]}
          />
        </div>
      </div>
    </ToolPage>
  )
})

TransferForm.propTypes = {
  path: T.string.isRequired,
  title: T.string.isRequired,
  explanation: T.object,
  openForm: T.func.isRequired,
  contextData: T.object
}

export {
  TransferForm
}
