import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'
import {schemeCategory20c} from '#/main/theme/color/utils'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ContentCounter} from '#/main/app/content/components/counter'
import {ToolPage} from '#/main/core/tool/containers/page'

import {LogFunctionalList} from '#/main/log/components/functional-list'
import {selectors} from '#/main/community/tools/community/activity/store'

class ActivityMain extends Component {
  constructor(props) {
    super(props)

    this.state = {
      loaded: false
    }
  }

  componentDidMount() {
    this.props.fetch(this.props.contextId).then(() => this.setState({loaded: true}))
  }

  render() {
    return (
      <ToolPage
        path={[{
          type: LINK_BUTTON,
          label: trans('activity'),
          target: `${this.props.path}/activity`
        }]}
        subtitle={trans('activity')}
      >
        <div className="d-flex flex-direction-row">
          <ContentCounter
            icon="fa fa-user"
            label={trans('users')}
            color={schemeCategory20c[1]}
            value={!this.state.loaded ? '?' : this.props.count.users}
          />

          <ContentCounter
            icon="fa fa-users"
            label={trans('groups')}
            color={schemeCategory20c[5]}
            value={!this.state.loaded ? '?' : this.props.count.groups}
          />
        </div>
        <LogFunctionalList
          className="component-container"
          name={selectors.STORE_NAME + '.logs'}
          url={['apiv2_community_functional_logs', {contextId: this.props.contextId}]}
          customDefinition={[
            {
              name: 'workspace',
              type: 'workspace',
              label: trans('workspace'),
              displayable: isEmpty(this.props.contextId),
              displayed: isEmpty(this.props.contextId)
            }, {
              name: 'resource',
              type: 'resource',
              label: trans('resource'),
              displayed: true
            }
          ]}
        />
      </ToolPage>
    )
  }
}

ActivityMain.propTypes = {
  path: T.string.isRequired,
  contextId: T.string.isRequired,
  count: T.shape({
    users: T.number,
    groups: T.number
  }).isRequired,
  fetch: T.func.isRequired
}

export {
  ActivityMain
}
