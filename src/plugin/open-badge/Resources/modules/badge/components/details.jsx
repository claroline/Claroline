import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {PageSection} from '#/main/app/page/components/section'
import {Content} from '#/main/app/components/content'

import {Assertion as AssertionTypes, Badge as BadgeTypes, Evidence as EvidenceTypes} from '#/plugin/open-badge/prop-types'
import {AssertionUserCard} from '#/plugin/open-badge/assertion/components/card'
import {BadgeMyAssertion} from '#/plugin/open-badge/badge/components/my-assertion'
import {AssertionList} from '#/plugin/open-badge/assertion/components/list'
import {selectors}  from '#/plugin/open-badge/tools/badges/store'
import {BadgeRuleDisplay} from '#/plugin/open-badge/badge-rule/components/display'
import {BadgeRule as BadgeRuleTypes} from '#/plugin/open-badge/badge-rule/prop-types'
import {TooltipOverlay} from '#/main/app/overlays/tooltip/components/overlay'

const BadgeRules = ({
  contextType,
  contextId = null,
  rules = [],
  evidences = []
}) => {
  return (
    <ul className="list-unstyled mb-0 d-flex flex-column gap-1">
      {rules.map(rule => {
        const granted = -1 !== evidences.findIndex(e => e.rule.type === rule.type)

        return (
          <li key={rule.id} className={classes('px-3 py-3 d-flex flex-row align-items-start gap-3 rounded-2 border', {
            'border-primary bg-primary-subtle text-primary-emphasis': granted,
            'border-transparent bg-body-tertiary': !granted
          })}>
            <BadgeRuleDisplay
              className="flex-fill"
              contextType={contextType}
              contextId={contextId}
              rule={rule}
            />
            <TooltipOverlay
              id={rule.id}
              tip={trans(granted ? 'rule_granted' : 'rule_not_granted', {}, 'badge')}
              position="left"
            >
              <span className={classes('fa fa-fw lh-base fs-lg my-n1', {
                'fa-circle-check text-primary': granted,
                'far fa-circle text-body-tertiary': !granted
              })} aria-hidden={true} />
            </TooltipOverlay>
          </li>
        )
      })}
    </ul>
  )
}

BadgeRules.propTypes = {
  contextType: T.string.isRequired,
  contextId: T.string,
  rules: T.arrayOf(T.shape(BadgeRuleTypes.propTypes)),
  evidences: T.arrayOf(T.shape(
    EvidenceTypes.propTypes
  ))
}

const BadgeDetails = (props) => {
  return (
    <>
      <PageSection className="mb-5">
        <BadgeMyAssertion assertion={props.assertion} />
      </PageSection>

      {(get(props.badge, 'meta.descriptionHtml') || !isEmpty(get(props.badge, 'tags'))) &&
        <PageSection className="pb-5">
          <Content
            tags={get(props.badge, 'tags')}
          >
            {get(props.badge, 'meta.descriptionHtml')}
          </Content>
        </PageSection>
      }

      <PageSection
        title={trans('Comment obtenir ce badge ?', {}, 'badge')}
        className="mb-5"
      >
        {!isEmpty(props.badge.rules) &&
          <BadgeRules
            rules={props.badge.rules}
            evidences={props.evidences}
            contextType={props.contextType}
            contextId={props.contextId}
          />
        }
      </PageSection>

      {(hasPermission('follow', props.badge) || !get(props.badge, 'restrictions.hideRecipients')) &&
        <PageSection
          title={trans('Utilisateurs ayant obtenu ce badge', {}, 'badges')}
          className="mb-5"
        >
          <AssertionList
            path={props.path}
            name={selectors.FORM_NAME + '.assertions'}
            url={['apiv2_badge_list_assertions', {badge: props.badge.id}]}
            primaryAction={undefined}
            customDefinition={[
              {
                name: 'user',
                type: 'user',
                label: trans('user'),
                alias: 'recipient',
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
                name: 'recipient.disabled',
                label: trans('user_disabled', {}, 'community'),
                type: 'boolean',
                displayable: false,
                sortable: false,
                filterable: true
              }
            ]}
            card={AssertionUserCard}
          />
        </PageSection>
      }
    </>
  )
}

BadgeDetails.propTypes = {
  path: T.string.isRequired,
  contextType: T.string.isRequired,
  contextId: T.string,
  badge: T.shape(
    BadgeTypes.propTypes
  ).isRequired,
  assertion: T.shape(
    AssertionTypes.propTypes
  ),
  evidences: T.arrayOf(T.shape(
    EvidenceTypes.propTypes
  ))
}

export {
  BadgeDetails
}
