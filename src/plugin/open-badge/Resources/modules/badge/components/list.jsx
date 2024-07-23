import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import merge from 'lodash/merge'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {ListData} from '#/main/app/content/list/containers/data'
import {constants as listConstants} from '#/main/app/content/list/constants'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {actions as listActions} from '#/main/app/content/list/store'

import {getActions, getDefaultAction} from '#/plugin/open-badge/badge/utils'
import {BadgeCard} from '#/plugin/open-badge/badge/components/card'
import {BadgeImage} from '#/plugin/open-badge/badge/components/image'

const Badges = (props) => {
  const refresher = merge({
    add:    () => props.invalidate(props.name),
    update: () => props.invalidate(props.name),
    delete: () => props.invalidate(props.name)
  }, props.refresher || {})

  return (
    <ListData
      primaryAction={(row) => getDefaultAction(row, refresher, props.path, props.currentUser)}
      actions={(rows) => getActions(rows, refresher, props.path, props.currentUser)}
      definition={[
        {
          name: 'name',
          label: trans('name'),
          displayed: true,
          primary: true,
          render: (badge) => (
            <div className="d-flex flex-direction-row gap-3 align-items-center">
              <BadgeImage badge={badge} size="xs" />
              {badge.name}
            </div>
          )
        }, {
          name: 'meta.description',
          type: 'string',
          label: trans('description'),
          options: {long: true},
          displayed: true,
          sortable: false
        }, {
          name: 'meta.enabled',
          label: trans('enabled'),
          type: 'boolean'
        }, {
          name: 'assignable',
          label: trans('assignable', {}, 'badge'),
          type: 'boolean',
          displayed: false,
          displayable: false,
          filterable: true
        }, {
          name: 'meta.createdAt',
          type: 'date',
          label: trans('creation_date'),
          options: {time: true},
          filterable: false,
          sortable: false
        }, {
          name: 'meta.updatedAt',
          type: 'date',
          label: trans('modification_date'),
          options: {time: true},
          filterable: false,
          sortable: false
        }, {
          name: 'tags',
          type: 'tag',
          label: trans('tags'),
          sortable: false,
          options: {
            objectClass: 'Claroline\\OpenBadgeBundle\\Entity\\BadgeClass'
          }
        }
      ].concat(props.customDefinition)}
      display={{current: listConstants.DISPLAY_LIST_SM}}

      {...omit(props, 'path', 'url', 'customDefinition', 'customActions', 'refresher', 'invalidate')}

      name={props.name}
      fetch={{
        url: props.url,
        autoload: true
      }}
      card={BadgeCard}
    />
  )
}

Badges.propTypes = {
  path: T.string,
  name: T.string.isRequired,
  autoload: T.bool,
  url: T.oneOfType([T.string, T.array]).isRequired,
  customDefinition: T.arrayOf(T.shape({
    // data list prop types
  }))
}

Badges.defaultProps = {
  customDefinition: []
}

const BadgeList = connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state)
  }),
  (dispatch) => ({
    invalidate(name) {
      dispatch(listActions.invalidateData(name))
    }
  })
)(Badges)

export {
  BadgeList
}
