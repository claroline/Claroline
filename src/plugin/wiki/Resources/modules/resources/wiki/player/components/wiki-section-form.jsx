import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'
import {selectors as formSelect} from '#/main/app/content/form/store'
import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {buildSectionMoveChoices} from '#/plugin/wiki/resources/wiki/utils'
import {selectors} from '#/plugin/wiki/resources/wiki/store/selectors'

// todo : use standard form buttons

const WikiSectionFormComponent = props =>
  <FormData
    className={'wiki-section-form'}
    level={3}
    name={selectors.STORE_NAME + '.sections.currentSection'}
    sections={[
      {
        icon: 'fa fa-fw fa-cog',
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'activeContribution.title',
            type: 'string',
            displayed: !props.isRoot,
            label: trans('title'),
            required: true
          }, {
            name: 'activeContribution.text',
            type: 'html',
            label: trans('text'),
            required: true,
            options: {
              workspace: props.workspace
            }
          }
        ]
      },
      {
        icon: 'fa fa-fw fa-arrows-v',
        title: trans('move_section', {}, 'icap_wiki'),
        displayed: !props.isRoot && !props.isNew,
        fields: [
          {
            name: 'move.direction',
            type: 'choice',
            label: trans('move', {}, 'icap_wiki'),
            required: false,
            options: {
              condensed: false,
              multiple: false,
              choices: {
                before: trans('before', {}, 'icap_wiki'),
                after: trans('after', {}, 'icap_wiki')
              }
            }
          }, {
            name: 'move.section',
            type: 'choice',
            label: trans('section', {}, 'icap_wiki'),
            options: {
              multiple: false,
              condensed: true,
              choices: props.sectionChoices
            }
          }
        ]
      }
    ]}
  >
    <div className="wiki-section-form-buttons text-center">
      <Button
        id="wiki-section-save-btn"
        icon="fa fa-fw fa-save"
        type={CALLBACK_BUTTON}
        className="btn"
        primary={true}
        disabled={!props.saveEnabled || !props.valid}
        callback={() => props.saveChanges(props.isNew)}
        label={trans(props.isNew ? 'create' : 'save')}
        title={trans(props.isNew ? 'create' : 'save')}
      />

      <Button
        id="wiki-section-cancel-btn"
        type={CALLBACK_BUTTON}
        className="btn"
        callback={() => props.cancelChanges()}
        label={trans('cancel', {}, 'actions')}
      />
    </div>
  </FormData>

WikiSectionFormComponent.propTypes = {
  workspace: T.object,
  cancelChanges: T.func.isRequired,
  saveChanges: T.func.isRequired,
  isNew: T.bool.isRequired,
  saveEnabled: T.bool.isRequired,
  valid: T.bool.isRequired,
  isRoot: T.bool.isRequired,
  sectionChoices: T.object.isRequired
}

const WikiSectionForm = connect(
  state => ({
    workspace: resourceSelectors.workspace(state),
    isNew: formSelect.isNew(formSelect.form(state, selectors.STORE_NAME + '.sections.currentSection')),
    saveEnabled: formSelect.saveEnabled(formSelect.form(state, selectors.STORE_NAME + '.sections.currentSection')),
    valid: formSelect.valid(formSelect.form(state, selectors.STORE_NAME + '.sections.currentSection')),
    isRoot: formSelect.data(formSelect.form(state, selectors.STORE_NAME + '.sections.currentSection')).id === selectors.sectionsTree(state).id,
    sectionChoices: buildSectionMoveChoices(selectors.sectionsTree(state), formSelect.data(formSelect.form(state, selectors.STORE_NAME + '.sections.currentSection')).id)
  })
)(WikiSectionFormComponent)

export {
  WikiSectionForm
}
