import React from 'react'
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
import {PageSection} from '#/main/app/page/components/section'
import {Alert} from '#/main/app/components/alert'

const BadgeDetailsComponent = (props) => {
  return (
    <>
      {props.badge.description &&
        <PageSection size="md" className="pb-5">
          <ContentHtml className="lead">{props.badge.description}</ContentHtml>

          {!isEmpty(props.badge.tags) &&
            <div className="mt-4" role="presentation">
              {props.badge.tags.map(tag =>
                <span key={tag} className="badge text-secondary-emphasis bg-secondary-subtle fs-sm lh-base">{tag}</span>
              )}
            </div>
          }
        </PageSection>
      }

      <PageSection
        size="md"
        className="bg-body-tertiary py-3"
        title={trans('Comment obtenir ce badge ?', {}, 'badge')}
      >
        {get(props.badge, 'meta.archived', false) &&
          <Alert type="info">
            {trans('badge_archived_help', {}, 'badge')}
          </Alert>
        }

        <div className="card">
          <ContentHtml className="card-body">{!isEmpty(props.badge.criteria) ? props.badge.criteria : trans('no_criteria', {}, 'badge')}</ContentHtml>
        </div>
      </PageSection>

      {(hasPermission('grant', props.badge) || !get(props.badge, 'restrictions.hideRecipients')) &&
        <PageSection
          size="md"
          className="py-3"
          title={trans('Utilisateurs ayant obtenu ce badge', {}, 'badges')}
        >
          <ListData
            name={selectors.FORM_NAME + '.assertions'}
            fetch={{
              url: ['apiv2_badge_list_assertions', {badge: props.badge.id}],
              autoload: !isEmpty(props.badge)
            }}
            primaryAction={(row) => ({
              type: LINK_BUTTON,
              target: props.path + `/badges/${props.badge.id}/assertion/${row.id}`,
              label: trans('open', {}, 'actions')
            })}
            delete={{
              url: ['apiv2_badge_remove_users', {badge: props.badge.id}],
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
              current: listConst.DISPLAY_LIST_SM
            }}
          />
        </PageSection>
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
    null,
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
