import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {select as formSelect} from '#/main/core/data/form/selectors'
import {Button} from '#/main/app/action'
import {FormContainer} from '#/main/core/data/form/containers/form'
import {buildSectionMoveChoices} from '#/plugin/wiki/resources/wiki/utils'

class WikiSectionFormComponent extends Component
{
  constructor(props) {
    super(props)
  }

  componentDidMount() {
    let node = document.getElementsByClassName('wiki-section-form')[0]
    if (node) {
      node.scrollIntoView({block: 'end', behavior: 'smooth'})
    }
  }
  
  render() {
    return (
      <FormContainer
        className={'wiki-section-form'}
        level={3}
        name="sections.currentSection"
        sections={[
          {
            icon: 'fa fa-fw fa-cog',
            title: trans('general'),
            primary: true,
            fields: [
              {
                name: 'activeContribution.title',
                type: 'string',
                displayed: !this.props.isRoot,
                label: trans('title'),
                required: true
              }, {
                name: 'activeContribution.text',
                type: 'html',
                label: trans('text'),
                required: true,
                options: {
                  min: 1
                }
              }
            ]
          },
          {
            icon: 'fa fa-fw fa-arrows-v',
            title: trans('move_section', {}, 'icap_wiki'),
            displayed: !this.props.isRoot && !this.props.isNew,
            fields: [
              {
                name: 'move.direction',
                type: 'choice',
                label: trans('move'),
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
                  choices: this.props.sectionChoices
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
            type="callback"
            className="btn"
            primary={true}
            disabled={!this.props.saveEnabled || !this.props.valid}
            callback={() => this.props.saveChanges(this.props.isNew)}
            label={trans(this.props.isNew ? 'create' : 'save')}
            title={trans(this.props.isNew ? 'create' : 'save')}
          />

          <Button
            id="wiki-section-cancel-btn"
            type="callback"
            className="btn"
            callback={() => this.props.cancelChanges()}
            label={trans('cancel')}
            title={trans('cancel')}
          />
        </div>
      </FormContainer>     
    )
  }
}

WikiSectionFormComponent.propTypes = {
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
    isNew: formSelect.isNew(formSelect.form(state, 'sections.currentSection')),
    saveEnabled: formSelect.saveEnabled(formSelect.form(state, 'sections.currentSection')),
    valid: formSelect.valid(formSelect.form(state, 'sections.currentSection')),
    isRoot: formSelect.data(formSelect.form(state, 'sections.currentSection')).id === state.sections.tree.id,
    sectionChoices: buildSectionMoveChoices(state.sections.tree, formSelect.data(formSelect.form(state, 'sections.currentSection')).id)
  })
)(WikiSectionFormComponent)

export {
  WikiSectionForm
}

  