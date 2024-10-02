import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {ContentInfoBlocks} from '#/main/app/content/components/info-block'
import {ToolPage} from '#/main/core/tool'

import {LogFunctionalList} from '#/main/log/components/functional-list'
import {selectors} from '#/main/community/tools/community/activity/store'
import {PageListSection, PageSection} from '#/main/app/page'

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
        title={trans('activity')}
      >
        <PageSection size="full">
          <ContentInfoBlocks
            className="my-4"
            size="lg"
            items={[
              {
                icon: 'fa fa-user',
                label: trans('users', {}, 'community'),
                value: !this.state.loaded ? '?' : this.props.count.users
              }, {
                icon: 'fa fa-users',
                label: trans('groups', {}, 'community'),
                value: !this.state.loaded ? '?' : this.props.count.groups
              }
            ]}
          />
        </PageSection>

        {/*<div className="row py-4" role="presentation">
          <ContentSizing size="md">
            <Activity />
          </ContentSizing>
        </div>*/}

        <PageListSection>
          <LogFunctionalList
            flush={true}
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
        </PageListSection>
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
