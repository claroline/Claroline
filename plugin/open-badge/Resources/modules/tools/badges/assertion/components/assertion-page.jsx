import React from 'react'
import {connect} from 'react-redux'
import {trans} from '#/main/app/intl/translation'

import {FormData} from '#/main/app/content/form/containers/data'
import {FormSections, FormSection} from '#/main/app/content/form/components/sections'
import {ListData} from '#/main/app/content/list/containers/data'
import {MODAL_BUTTON} from '#/main/app/buttons'

import {EvidenceList} from '#/plugin/open-badge/tools/badges/evidence/components/evidence-list'
import {MODAL_BADGE_EVIDENCE} from '#/plugin/open-badge/tools/badges/modals/evidence'
import {selectors as evidenceSelectors} from '#/plugin/open-badge/tools/badges/modals/evidence/store/selectors'
import {actions as formActions} from '#/main/app/content/form/store'

import {
  selectors as formSelect
} from '#/main/app/content/form/store'

// TODO : add tools
const AssertionPageComponent = (props) => {
  return (
    <FormData
      {...props}
      name="badges.assertion"
      meta={false}
      buttons={true}
      target={(assertion, isNew) => isNew ?
        ['apiv2_assertion_create'] :
        ['apiv2_assertion_update', {id: assertion.id}]
      }
      sections={[
        {
          title: trans('assertion'),
          primary: true,
          fields: [
            {
              name: 'user',
              type: 'user',
              disabled: true,
              required: true
            },
            {
              name: 'badge',
              type: 'badge',
              disabled: true,
              required: true
            }
          ]
        }
      ]}
    >
      <FormSections
        level={3}
      >
        <FormSection
          className="embedded-list-section"
          icon="fa fa-fw fa-user"
          title={trans('evidences')}
          disabled={props.new}
          actions={[{
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
            name="badges.assertion.evidences"
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
    </FormData>)
}



const AssertionPage = connect(
  (state) => ({
    currentContext: state.currentContext,
    new: formSelect.isNew(formSelect.form(state, 'badges.assertion')),
    assertion: formSelect.data(formSelect.form(state, 'badges.assertion'))
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
)(AssertionPageComponent)

export {
  AssertionPage as Assertion
}
