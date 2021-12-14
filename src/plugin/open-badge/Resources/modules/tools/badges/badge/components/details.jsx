import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {url} from '#/main/app/api'
import {withRouter} from '#/main/app/router'
import {trans, transChoice} from '#/main/app/intl/translation'
import {ContentHtml} from '#/main/app/content/components/html'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, DOWNLOAD_BUTTON, MODAL_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'
import {constants as listConst} from '#/main/app/content/list/constants'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'
import {MODAL_USERS} from '#/main/core/modals/users'

import {BadgeLayout}  from '#/plugin/open-badge/tools/badges/badge/components/layout'
import {Badge as BadgeTypes} from '#/plugin/open-badge/prop-types'
import {AssertionUserCard} from '#/plugin/open-badge/tools/badges/assertion/components/card'
import {actions as badgeActions}  from '#/plugin/open-badge/tools/badges/store'
import {actions, selectors}  from '#/plugin/open-badge/tools/badges/badge/store'

const BadgeDetailsComponent = (props) => {
  const sections = [
    {
      name: 'granting',
      icon: 'fa fa-fw fa-certificate',
      label: trans('award_rules', {}, 'badge'),
      render() {
        return (
          <Fragment>
            {!props.badge.meta.enabled &&
              <div className="alert alert-info">
                <span className="fa fa-fw fa-info-circle icon-with-text-right" />
                {trans('badge_disabled_help', {}, 'badge')}
              </div>
            }

            <div className="panel panel-default">
              <ContentHtml className="panel-body">{!isEmpty(props.badge.criteria) ? props.badge.criteria : trans('no_criteria', {}, 'badge')}</ContentHtml>
            </div>

            {get(props.badge, 'permissions.grant') &&
              <Button
                className="btn btn-block btn-emphasis component-container"
                type={MODAL_BUTTON}
                label={trans('grant_users', {}, 'badge')}
                disabled={!props.badge.meta.enabled}
                modal={[MODAL_USERS, {
                  selectAction: (selected) => ({
                    type: CALLBACK_BUTTON,
                    label: trans('select', {}, 'actions'),
                    callback: () => props.grant(props.badge.id, selected)
                  })
                }]}
                primary={true}
              />
            }
          </Fragment>
        )
      }
    }
  ]

  if (get(props.badge, 'permissions.edit') || !get(props.badge, 'restrictions.hideRecipients')) {
    sections.unshift({
      name: 'activity',
      label: trans('activity'),
      render() {
        return (
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
        )
      }
    })
  }

  return (
    <BadgeLayout
      badge={props.badge}
      assertion={null}

      backAction={{
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-arrow-left',
        label: trans('back'),
        tooltip: 'bottom',
        target: `${props.path}/badges`,
        exact: true
      }}
      actions={[
        {
          name: 'edit',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-pencil',
          label: trans('edit', {}, 'actions'),
          target: props.path + `/badges/${props.badge.id}/edit`,
          displayed: get(props.badge, 'permissions.edit'),
          group: trans('management')
        }, {
          name: 'recalculate',
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-refresh',
          label: trans('recalculate', {}, 'actions'),
          callback: () => props.recalculate(props.badge.id),
          displayed: get(props.badge, 'permissions.grant') && !isEmpty(props.badge.rules),
          disabled:  !get(props.badge, 'meta.enabled'),
          group: trans('management')
        }, {
          name: 'export-results',
          type: DOWNLOAD_BUTTON,
          icon: 'fa fa-fw fa-file-csv',
          label: trans('export', {}, 'actions'),
          displayed: get(props.badge, 'permissions.grant'),
          file: {
            url: url(['apiv2_assertion_csv'], {
              filters: {badge: get(props.badge, 'id')},
              columns: [
                'user.firstName',
                'user.lastName',
                'user.email',
                'issuedOn',
                'expires'
              ]
            })
          },
          group: trans('transfer')
        }, {
          name: 'enable',
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-check-circle',
          label: trans('enable', {}, 'actions'),
          displayed: get(props.badge, 'permissions.edit') && !get(props.badge, 'meta.enabled'),
          callback: () => props.enable(props.badge),
          group: trans('management')
        }, {
          name: 'disable',
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-times-circle',
          label: trans('disable', {}, 'actions'),
          displayed: get(props.badge, 'permissions.edit') && get(props.badge, 'meta.enabled'),
          callback: () => props.disable(props.badge),
          confirm: {
            title: transChoice('disable_badges', 1, {count: 1}),
            message: trans('disable_badges_confirm', {badges_list: props.badge.name})
          },
          group: trans('management')
        }, {
          name: 'delete',
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-trash-o',
          label: trans('delete', {}, 'actions'),
          dangerous: true,
          displayed: get(props.badge, 'permissions.delete'),
          confirm: {
            title: trans('objects_delete_title'),
            message: transChoice('objects_delete_question', 1, {count: 1}),
            button: trans('delete', {}, 'actions')
          },
          callback: () => props.delete(props.badge).then(() => {
            props.history.push(props.path+'/badges')
          }),
          group: trans('management')
        }
      ]}

      sections={sections}
    />
  )
}

BadgeDetailsComponent.propTypes = {
  history: T.shape({
    push: T.func.isRequired
  }).isRequired,
  path: T.string.isRequired,
  badge: T.shape(
    BadgeTypes.propTypes
  ),
  enable: T.func.isRequired,
  disable: T.func.isRequired,
  delete: T.func.isRequired,
  grant: T.func.isRequired,
  recalculate: T.func.isRequired,
  downloadAssertion: T.func.isRequired
}

BadgeDetailsComponent.defaultProps = {
  badge: {}
}

const BadgeDetails = withRouter(
  connect(
    (state) => ({
      path: toolSelectors.path(state),
      badge: formSelectors.data(formSelectors.form(state, selectors.FORM_NAME))
    }),
    (dispatch) =>({
      enable(badge) {
        dispatch(actions.enable([badge])).then(response => {
          dispatch(formActions.resetForm(selectors.FORM_NAME, response[0]))
        })
      },
      disable(badge) {
        return dispatch(actions.disable([badge])).then(response => {
          dispatch(formActions.resetForm(selectors.FORM_NAME, response[0]))
        })
      },
      delete(badge) {
        return dispatch(actions.delete([badge]))
      },

      grant(badgeId, selected) {
        dispatch(actions.grant(badgeId, selected))
      },
      recalculate(badgeId) {
        dispatch(actions.recalculate(badgeId))
      },
      downloadAssertion(assertion) {
        dispatch(badgeActions.downloadAssertion(assertion))
      }
    })
  )(BadgeDetailsComponent)
)

export {
  BadgeDetails
}
