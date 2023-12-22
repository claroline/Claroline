import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {withRouter} from '#/main/app/router'
import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {ContentHtml} from '#/main/app/content/components/html'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'
import {constants as listConst} from '#/main/app/content/list/constants'

import {Badge as BadgeTypes} from '#/plugin/open-badge/prop-types'
import {AssertionUserCard} from '#/plugin/open-badge/tools/badges/assertion/components/card'
import {actions as badgeActions}  from '#/plugin/open-badge/tools/badges/store'
import {selectors}  from '#/plugin/open-badge/tools/badges/store'
import {ContentSizing} from '#/main/app/content/components/sizing'

const BadgeDetailsComponent = (props) => {
  return (
    <>
      {props.badge.description &&
        <div className="row">
          <ContentSizing size="md" className="mb-5">
            <ContentHtml className="lead">{props.badge.description}</ContentHtml>
          </ContentSizing>
        </div>
      }

      <div className="row bg-body-tertiary">
        <ContentSizing size="md" className="my-3">
          <h2>Comment obtenir ce badge ?</h2>

          {!props.badge.meta.enabled &&
            <div className="alert alert-info">
              <span className="fa fa-fw fa-circle-info icon-with-text-right" />
              {trans('badge_disabled_help', {}, 'badge')}
            </div>
          }

          <div className="card">
            <ContentHtml className="card-body">{!isEmpty(props.badge.criteria) ? props.badge.criteria : trans('no_criteria', {}, 'badge')}</ContentHtml>
          </div>
        </ContentSizing>
      </div>

      {(hasPermission('edit', props.badge) || !get(props.badge, 'restrictions.hideRecipients')) &&
        <div className="row">
          <ContentSizing size="md" className="my-3">
            <h2>Utilisateurs ayant obtenu ce badge</h2>
            <ListData
              name={selectors.FORM_NAME + '.assertions'}
              fetch={{
                url: ['apiv2_badge-class_assertion', {badge: props.badge.id}],
                autoload: !isEmpty(props.badge)
              }}
              primaryAction={(row) => ({
                type: LINK_BUTTON,
                target: props.path + `/badges/${props.badge.id}/assertion/${row.id}`,
                label: trans('open', {}, 'actions')
              })}
              delete={{
                url: ['apiv2_badge-class_remove_users', {badge: props.badge.id}],
                displayed: () => get(props.badge, 'permissions.grant')
              }}
              definition={[
                {
                  name: 'user',
                  type: 'user',
                  label: trans('user'),
                  displayed: true
                }, {
                  name: 'user.email',
                  type: 'email',
                  label: trans('email'),
                  sortable: false,
                  filterable: false
                }, {
                  name: 'issuedOn',
                  label: trans('granted_date', {}, 'badge'),
                  type: 'date',
                  displayed: true,
                  primary: true,
                  options: {
                    time: true
                  }
                }, {
                  name: 'userDisabled',
                  label: trans('user_disabled', {}, 'community'),
                  type: 'boolean',
                  displayable: false,
                  sortable: false,
                  filterable: true
                }
              ]}
              actions={(rows) => [
                {
                  type: CALLBACK_BUTTON,
                  icon: 'fa fa-fw fa-download',
                  label: trans('download', {}, 'actions'),
                  callback: () => rows.map(row => props.downloadAssertion(row)),
                  displayed: get(props.badge, 'permissions.grant')
                }
              ]}
              card={AssertionUserCard}
              display={{
                current: listConst.DISPLAY_LIST_SM,
                available: [
                  listConst.DISPLAY_TABLE_SM,
                  listConst.DISPLAY_TABLE,
                  listConst.DISPLAY_LIST_SM,
                  listConst.DISPLAY_TILES_SM
                ]
              }}
            />
          </ContentSizing>
        </div>
      }
    </>
  )
}

BadgeDetailsComponent.propTypes = {
  history: T.shape({
    push: T.func.isRequired
  }).isRequired,
  path: T.string.isRequired,
  badge: T.shape(
    BadgeTypes.propTypes
  ).isRequired,
  downloadAssertion: T.func.isRequired
}

BadgeDetailsComponent.defaultProps = {
  badge: {}
}

const BadgeDetails = withRouter(
  connect(
    (state) => null,
    (dispatch) =>({
      downloadAssertion(assertion) {
        dispatch(badgeActions.downloadAssertion(assertion))
      }
    })
  )(BadgeDetailsComponent)
)

export {
  BadgeDetails
}
