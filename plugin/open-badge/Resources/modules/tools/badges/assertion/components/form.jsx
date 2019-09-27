import React from 'react'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'
import {FormSections, FormSection} from '#/main/app/content/form/components/sections'
import {ListData} from '#/main/app/content/list/containers/data'
import {MODAL_BUTTON} from '#/main/app/buttons'

import {BadgeCard} from '#/plugin/open-badge/tools/badges/badge/components/card'
import {UserCard} from '#/main/core/user/components/card'
import {EvidenceList} from '#/plugin/open-badge/tools/badges/evidence/components/definition'
import {MODAL_BADGE_EVIDENCE} from '#/plugin/open-badge/tools/badges/modals/evidence'
import {selectors as evidenceSelectors} from '#/plugin/open-badge/tools/badges/modals/evidence/store/selectors'
import {actions as formActions} from '#/main/app/content/form/store'
import {selectors}  from '#/plugin/open-badge/tools/badges/store/selectors'

import {
  selectors as formSelect
} from '#/main/app/content/form/store'

const AssertionFormComponent = (props) =>
  <div>
    {props.assertion.badge &&
      <BadgeCard data={props.assertion.badge}/>
    }

    {props.assertion.user &&
      <UserCard data={props.assertion.user}/>
    }

    <FormData
      {...props}
      name={selectors.STORE_NAME + '.badges.assertion'}
      meta={false}
      buttons={false}
      target={(assertion) => ['apiv2_assertion_update', {id: assertion.id}]}
      sections={[]}
    >
      <FormSections
        level={3}
      >
        <FormSection
          className="embedded-list-section"
          icon="fa fa-fw fa-user"
          title={trans('evidences', {}, 'badge')}
          disabled={props.new}
          actions={[{
            name: 'add',
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add_evidence'),
            modal: [MODAL_BADGE_EVIDENCE, {
              assertion: props.assertion,
              initForm: props.initForm
            }]
          }]}
        >
          <ListData
            name={selectors.STORE_NAME + '.badges.assertion.evidences'}
            fetch={{
              url: ['apiv2_assertion_evidences', {assertion: props.assertion.id}],
              autoload: props.assertion.id && !props.new
            }}
            primaryAction={(row) => ({
              type: MODAL_BUTTON,
              modal: [MODAL_BADGE_EVIDENCE, {
                evidence: row,
                assertion: props.assertion,
                initForm: props.initForm
              }]
            })}
            delete={{
              url: ['apiv2_evidence_delete_bulk']
            }}
            definition={EvidenceList.definition}
            card={EvidenceList.card}

          />
        </FormSection>
      </FormSections>
    </FormData>
  </div>

const AssertionForm = connect(
  (state) => ({
    currentContext: state.currentContext,
    new: false,
    assertion: formSelect.data(formSelect.form(state, selectors.STORE_NAME + '.badges.assertion'))
  }),
  dispatch => ({
    initForm(evidence = null) {
      if (!evidence) {
        dispatch(formActions.resetForm(evidenceSelectors.STORE_NAME, {}, true))
      } else {
        dispatch(formActions.resetForm(evidenceSelectors.STORE_NAME, evidence, false))
      }
    }
  })
)(AssertionFormComponent)

export {
  AssertionForm
}
