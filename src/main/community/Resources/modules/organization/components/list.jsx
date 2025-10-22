import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import omit from 'lodash/omit'
import merge from 'lodash/merge'

import {trans} from '#/main/app/intl/translation'
import {Badge} from '#/main/app/components/badge'
import {DataMicro} from '#/main/app/data/components/micro'
import {ListData, actions as listActions, constants as listConst} from '#/main/app/content/list'
import {selectors as securitySelectors} from '#/main/app/security/store'

import {getActions, getDefaultAction} from '#/main/community/organization/utils'
import {OrganizationCard} from '#/main/community/organization/components/card'

const OrganizationListComponent = props => {
  const refresher = merge({
    add:    () => props.invalidate(props.name),
    update: () => props.invalidate(props.name),
    delete: () => props.invalidate(props.name)
  }, props.refresher || {})

  return (
    <ListData
      primaryAction={(row) => getDefaultAction(row, refresher, props.path, props.currentUser)}
      actions={(rows) => getActions(rows, refresher, props.path, props.currentUser).then((actions) => [].concat(actions, props.customActions(rows)))}
      definition={[
        {
          name: 'name',
          type: 'string',
          label: trans('name'),
          displayed: true,
          primary: true,
          render: (organization) => {
            return (
              <div className="d-flex align-items-center" role="presentation">
                <DataMicro object={organization}/>
                {get(organization, 'meta.default') &&
                  <Badge className="ms-2 top-0" variant="primary">{trans('default')}</Badge>
                }
              </div>
            )
          }
        }, {
          name: 'code',
          type: 'string',
          label: trans('code')
        }, {
          name: 'meta.description',
          type: 'string',
          label: trans('description'),
          options: {long: true},
          displayed: true,
          sortable: false
        }, {
          name: 'meta.default',
          type: 'boolean',
          label: trans('default'),
          displayable: false,
          sortable: false,
          filterable: true
        }, {
          name: 'email',
          type: 'email',
          label: trans('email')
        }, {
          name: 'meta.public',
          alias: 'public',
          type: 'boolean',
          label: trans('public'),
          displayed: true
        }
      ].concat(props.customDefinition)}
      card={OrganizationCard}
      display={{
        current: listConst.DISPLAY_LIST
      }}

      {...omit(props, 'path', 'url', 'autoload', 'customDefinition', 'customActions', 'refresher', 'invalidate')}

      name={props.name}
      fetch={{
        url: props.url,
        autoload: props.autoload
      }}
    />
  )
}

OrganizationListComponent.propTypes = {
  path: T.string,
  name: T.string.isRequired,
  autoload: T.bool,
  url: T.oneOfType([T.string, T.array]).isRequired,
  customDefinition: T.arrayOf(T.shape({
    // data list prop types
  })),
  customActions: T.func,
  invalidate: T.func.isRequired,
  currentUser: T.object,
  refresher: T.shape({
    add: T.func,
    update: T.func,
    delete: T.func
  })
}

OrganizationListComponent.defaultProps = {
  autoload: true,
  customDefinition: [],
  customActions: () => []
}

const OrganizationList = connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state)
  }),
  (dispatch) => ({
    invalidate(name) {
      dispatch(listActions.invalidateData(name))
    }
  })
)(OrganizationListComponent)

export {
  OrganizationList
}
