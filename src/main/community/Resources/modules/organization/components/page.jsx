import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {ToolPage} from '#/main/core/tool'
import {LINK_BUTTON} from '#/main/app/buttons'
import {selectors as securitySelectors} from '#/main/app/security/store'

import {getActions} from '#/main/community/organization/utils'
import {Organization as OrganizationTypes} from '#/main/community/organization/prop-types'
import {ContentLoader} from '#/main/app/content/components/loader'
import {PageHeading} from '#/main/app/page/components/heading'

const Organization = (props) =>
  <ToolPage
    breadcrumb={[
      {
        type: LINK_BUTTON,
        label: trans('organizations', {}, 'community'),
        target: `${props.path}/organizations`
      }
    ].concat(props.organization ? props.breadcrumb : [])}
    poster={get(props.organization, 'poster')}
    title={trans('organization_name', {name: get(props.organization, 'name', trans('loading'))}, 'community')}
    description={get(props.organization, 'meta.description')}
  >
    {isEmpty(props.organization) &&
      <ContentLoader
        size="lg"
        description={trans('organization_loading', {}, 'community')}
      />
    }

    {!isEmpty(props.organization) &&
      <PageHeading
        size="md"
        title={get(props.organization, 'name', trans('loading'))}
        primaryAction="edit"
        actions={!isEmpty(props.organization) ? getActions([props.organization], {
          add: () => props.reload(props.organization.id),
          update: () => props.reload(props.organization.id),
          delete: () => props.reload(props.organization.id)
        }, props.path, props.currentUser) : []}
      />
    }

    {!isEmpty(props.organization) && props.children}
  </ToolPage>

Organization.propTypes = {
  path: T.string,
  breadcrumb: T.array,
  organization: T.shape(
    OrganizationTypes.propTypes
  ),
  currentUser: T.object,
  children: T.any,
  reload: T.func
}

Organization.defaultProps = {
  breadcrumb: []
}

const OrganizationPage = connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state)
  })
)(Organization)

export {
  OrganizationPage
}
